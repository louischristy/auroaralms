<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\LessonCompletion;
use App\Models\Lesson;
use App\Models\ScormTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScormApiController extends Controller
{
    /**
     * Initialize — return existing CMI data or defaults for a SCORM lesson.
     */
    public function initialize(Request $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();

        $tracking = ScormTracking::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'course_id'  => $lesson->course_id,
                'tenant_id'  => $user->tenant_id,
                'entry_type' => 'ab-initio',
            ]
        );

        // If resuming a suspended session
        if ($tracking->exit_type === 'suspend' && ! $tracking->wasRecentlyCreated) {
            $tracking->update(['entry_type' => 'resume']);
        }

        return response()->json([
            'success' => true,
            'cmi' => [
                'core' => [
                    'student_id'      => (string) $user->id,
                    'student_name'    => $user->name,
                    'lesson_location' => $tracking->lesson_location ?? '',
                    'lesson_status'   => $tracking->lesson_status,
                    'score'           => [
                        'raw' => $tracking->score_raw,
                        'min' => $tracking->score_min ?? 0,
                        'max' => $tracking->score_max ?? 100,
                    ],
                    'total_time'   => $this->secondsToScormTime($tracking->total_time_seconds),
                    'exit'         => $tracking->exit_type ?? '',
                    'entry'        => $tracking->entry_type ?? 'ab-initio',
                    'credit'       => 'credit',
                    'lesson_mode'  => 'normal',
                ],
                'suspend_data' => $tracking->suspend_data ?? '',
                'launch_data'  => '',
            ],
        ]);
    }

    /**
     * Commit — save CMI data sent by the SCORM content.
     */
    public function commit(Request $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'cmi' => 'required|array',
        ]);

        $cmi = $data['cmi'];
        $core = $cmi['core'] ?? [];

        $tracking = ScormTracking::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        if (! $tracking) {
            return response()->json(['success' => false, 'error' => 'Not initialized'], 400);
        }

        // Parse session time
        $sessionSeconds = 0;
        if (! empty($core['session_time'])) {
            $sessionSeconds = ScormTracking::parseScormTime($core['session_time']);
        }

        $tracking->update([
            'lesson_status'        => $core['lesson_status'] ?? $tracking->lesson_status,
            'lesson_location'      => $core['lesson_location'] ?? $tracking->lesson_location,
            'score_raw'            => $core['score']['raw'] ?? $tracking->score_raw,
            'score_min'            => $core['score']['min'] ?? $tracking->score_min,
            'score_max'            => $core['score']['max'] ?? $tracking->score_max,
            'session_time_seconds' => $sessionSeconds,
            'total_time_seconds'   => $tracking->total_time_seconds + $sessionSeconds,
            'suspend_data'         => $cmi['suspend_data'] ?? $tracking->suspend_data,
            'exit_type'            => $core['exit'] ?? $tracking->exit_type,
            'cmi_data'             => $cmi,
        ]);

        // Auto-complete lesson if SCORM reports completed/passed
        if ($tracking->isComplete()) {
            $this->autoCompleteLesson($user, $lesson, $tracking);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Finish — called on LMSFinish. Final commit + mark session ended.
     */
    public function finish(Request $request, Lesson $lesson): JsonResponse
    {
        // Commit any final data first
        if ($request->has('cmi')) {
            $this->commit($request, $lesson);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Auto-create a LessonCompletion and recalculate enrollment progress.
     */
    private function autoCompleteLesson($user, Lesson $lesson, ScormTracking $tracking): void
    {
        LessonCompletion::firstOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'course_id'          => $lesson->course_id,
                'completed_at'       => now(),
                'time_spent_seconds' => $tracking->total_time_seconds,
            ]
        );

        $enrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $lesson->course_id)
            ->first();

        if ($enrollment) {
            $enrollment->recalculateProgress();
        }
    }

    private function secondsToScormTime(int $seconds): string
    {
        $h = intdiv($seconds, 3600);
        $m = intdiv($seconds % 3600, 60);
        $s = $seconds % 60;
        return sprintf('%04d:%02d:%02d', $h, $m, $s);
    }
}
