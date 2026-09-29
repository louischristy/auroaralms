<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\PhishingTemplate;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizQuestion;
use App\Models\User;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiToolsController extends Controller
{
    public function index()
    {
        return view('client.ai.index');
    }

    // ── AI Course Generator ──

    public function courseGenerator()
    {
        return view('client.ai.course-generator');
    }

    public function generateCourse(Request $request, AiService $ai)
    {
        $request->validate([
            'topic' => 'required|string|max:200',
            'difficulty' => 'required|in:beginner,intermediate,advanced',
            'target_audience' => 'required|string|max:200',
            'lesson_count' => 'required|integer|min:2|max:8',
        ]);

        try {
            $data = $ai->generateCourseContent(
                $request->topic,
                $request->difficulty,
                $request->target_audience,
                $request->lesson_count
            );

            // Store in session for review before saving
            session(['ai_course_data' => $data]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function saveCourse(Request $request)
    {
        $data = session('ai_course_data');
        if (!$data) {
            return back()->with('error', 'No AI-generated course data found. Please generate a course first.');
        }

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $course = Course::create([
                'title' => $data['title'],
                'slug' => Str::slug($data['title']),
                'description' => $data['description'],
                'objectives' => $data['objectives'] ?? [],
                'category' => $data['category'] ?? 'Emerging Threats',
                'difficulty' => $request->input('difficulty', 'intermediate'),
                'duration_minutes' => $data['duration_minutes'] ?? 30,
                'passing_score' => 70,
                'is_active' => false, // Draft by default
                'tenant_id' => $user->tenant_id,
            ]);

            foreach (($data['lessons'] ?? []) as $i => $lessonData) {
                Lesson::create([
                    'course_id' => $course->id,
                    'title' => $lessonData['title'],
                    'slug' => Str::slug($lessonData['title']),
                    'content' => $lessonData['content'],
                    'content_type' => 'text',
                    'duration_minutes' => $lessonData['duration_minutes'] ?? 10,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                ]);
            }

            DB::commit();
            session()->forget('ai_course_data');

            return redirect()->route('manage.courses.edit', $course)
                ->with('success', 'AI-generated course created successfully! Review and publish when ready.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save course: ' . $e->getMessage());
        }
    }

    // ── AI Quiz Generator ──

    public function quizGenerator()
    {
        $user = Auth::user();
        $courses = Course::where(function ($q) use ($user) {
            $q->where('tenant_id', $user->tenant_id)->orWhereNull('tenant_id');
        })->with('lessons')->orderBy('title')->get();

        return view('client.ai.quiz-generator', compact('courses'));
    }

    public function generateQuiz(Request $request, AiService $ai)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'question_count' => 'required|integer|min:3|max:20',
        ]);

        $course = Course::with('lessons')->findOrFail($request->course_id);
        $lessonTitles = $course->lessons->pluck('title')->toArray();

        try {
            $data = $ai->generateQuizQuestions(
                $course->title,
                $course->description ?? '',
                $lessonTitles,
                $request->question_count
            );

            session(['ai_quiz_data' => $data, 'ai_quiz_course_id' => $course->id]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function saveQuiz(Request $request)
    {
        $data = session('ai_quiz_data');
        $courseId = session('ai_quiz_course_id');

        if (!$data || !$courseId) {
            return back()->with('error', 'No AI-generated quiz data found. Please generate a quiz first.');
        }

        $course = Course::findOrFail($courseId);

        DB::beginTransaction();
        try {
            // Create or update quiz
            $quiz = $course->quiz ?? new Quiz();
            $quiz->fill([
                'course_id' => $course->id,
                'title' => $data['title'] ?? "{$course->title} Quiz",
                'instructions' => $data['instructions'] ?? 'Answer all questions to the best of your ability.',
                'time_limit_minutes' => null,
                'max_attempts' => 3,
                'shuffle_questions' => true,
                'show_correct_answers' => true,
                'is_active' => true,
            ]);
            $quiz->save();

            $existingCount = $quiz->questions()->count();

            foreach (($data['questions'] ?? []) as $i => $qData) {
                $question = QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => $qData['question'],
                    'question_type' => $qData['question_type'] ?? 'multiple_choice',
                    'explanation' => $qData['explanation'] ?? null,
                    'points' => $qData['points'] ?? 10,
                    'sort_order' => $existingCount + $i + 1,
                ]);

                foreach (($qData['answers'] ?? []) as $j => $aData) {
                    QuizAnswer::create([
                        'question_id' => $question->id,
                        'answer_text' => $aData['answer_text'],
                        'is_correct' => $aData['is_correct'] ?? false,
                        'sort_order' => $j + 1,
                    ]);
                }
            }

            DB::commit();
            session()->forget(['ai_quiz_data', 'ai_quiz_course_id']);

            return redirect()->route('manage.courses.edit', $course)
                ->with('success', 'AI-generated quiz questions added successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save quiz: ' . $e->getMessage());
        }
    }

    // ── AI Phishing Template Generator ──

    public function phishingGenerator()
    {
        return view('client.ai.phishing-generator');
    }

    public function generatePhishing(Request $request, AiService $ai)
    {
        $request->validate([
            'scenario_type' => 'required|in:credential_harvest,malware_download,data_exfiltration,business_email_compromise,invoice_fraud,tech_support_scam',
            'difficulty' => 'required|in:easy,medium,hard',
            'industry' => 'required|string|max:100',
            'count' => 'required|integer|min:1|max:5',
        ]);

        try {
            $data = $ai->generatePhishingTemplates(
                $request->scenario_type,
                $request->difficulty,
                $request->industry,
                $request->count
            );

            session(['ai_phishing_data' => $data]);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function savePhishing(Request $request)
    {
        $data = session('ai_phishing_data');
        if (!$data || empty($data['templates'])) {
            return back()->with('error', 'No AI-generated phishing templates found. Please generate templates first.');
        }

        $saved = 0;
        foreach ($data['templates'] as $tpl) {
            PhishingTemplate::create([
                'name' => $tpl['name'],
                'scenario_type' => $tpl['scenario_type'] ?? 'credential_harvest',
                'subject' => $tpl['subject'],
                'sender_name' => $tpl['sender_name'],
                'sender_email' => $tpl['sender_email'],
                'body_html' => $tpl['body_html'],
                'landing_page_html' => $tpl['landing_page_html'] ?? '',
                'difficulty' => $tpl['difficulty'] ?? 'medium',
                'is_system' => false,
            ]);
            $saved++;
        }

        session()->forget('ai_phishing_data');

        return redirect()->route('manage.phishing.create')
            ->with('success', "{$saved} AI-generated phishing template(s) saved successfully!");
    }

    // ── Smart Risk Scoring ──

    public function riskDashboard(AiService $ai)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        // Get all users for this tenant
        $users = User::where('tenant_id', $tenantId)
            ->with(['courseEnrollments', 'quizAttempts', 'policyAcknowledgments'])
            ->role(['employee', 'manager'])
            ->get();

        $riskData = [];
        $riskDistribution = ['low' => 0, 'medium' => 0, 'high' => 0, 'critical' => 0];

        foreach ($users as $u) {
            $totalCourses = $u->courseEnrollments->count();
            $completedCourses = $u->courseEnrollments->where('status', 'completed')->count();
            $overdueCourses = $u->courseEnrollments->where('status', '!=', 'completed')
                ->where('due_date', '<', now())->count();

            $quizScores = $u->quizAttempts->pluck('score')->filter();
            $avgQuiz = $quizScores->count() > 0 ? $quizScores->avg() : 0;

            // Phishing data from phishing_results if table exists
            $phishingTotal = 0;
            $phishingClicks = 0;
            $phishingReported = 0;
            try {
                $phishingResults = DB::table('phishing_results')->where('user_id', $u->id)->get();
                $phishingTotal = $phishingResults->count();
                $phishingClicks = $phishingResults->whereIn('status', ['clicked', 'submitted'])->count();
                $phishingReported = $phishingResults->where('status', 'reported')->count();
            } catch (\Exception $e) {
                // Table might not exist yet
            }

            $totalPolicies = DB::table('policies')->where('tenant_id', $tenantId)->where('is_active', true)->count();
            $ackedPolicies = $u->policyAcknowledgments->count();
            $policyRate = $totalPolicies > 0 ? round(($ackedPolicies / $totalPolicies) * 100) : 100;

            $userData = [
                'course_completion_rate' => $totalCourses > 0 ? round(($completedCourses / $totalCourses) * 100) : 0,
                'avg_quiz_score' => round($avgQuiz),
                'phishing_clicks' => $phishingClicks,
                'phishing_total' => $phishingTotal,
                'phishing_reported' => $phishingReported,
                'policy_ack_rate' => $policyRate,
                'overdue_courses' => $overdueCourses,
            ];

            $risk = $ai->calculateRiskScore($userData);

            $riskData[] = [
                'user' => $u,
                'risk' => $risk,
                'stats' => $userData,
            ];

            $riskDistribution[$risk['level']]++;
        }

        // Sort by risk score descending
        usort($riskData, fn ($a, $b) => $b['risk']['score'] <=> $a['risk']['score']);

        $avgRiskScore = count($riskData) > 0
            ? round(collect($riskData)->avg('risk.score'))
            : 0;

        return view('client.ai.risk-dashboard', compact('riskData', 'riskDistribution', 'avgRiskScore'));
    }
}
