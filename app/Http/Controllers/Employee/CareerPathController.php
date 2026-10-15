<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CareerPath;
use App\Models\CareerPathEnrollment;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\ExternalCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CareerPathController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $paths = CareerPath::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->withCount('stages')
            ->ordered()
            ->get();

        $enrolledIds = CareerPathEnrollment::where('user_id', $user->id)
            ->pluck('career_path_id')
            ->all();

        return view('employee.career-paths.index', compact('paths', 'enrolledIds'));
    }

    public function show(int $id)
    {
        $user = Auth::user();

        $path = CareerPath::withoutTenantScope()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->with('stages')
            ->findOrFail($id);

        $enrollment = CareerPathEnrollment::where('user_id', $user->id)
            ->where('career_path_id', $id)
            ->first();

        // Gather user's completed course IDs and certification names for progress
        $completedCourseIds = \App\Models\CourseEnrollment::withoutTenantScope()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('course_id')
            ->all();

        $userCertNames = Certificate::withoutTenantScope()
            ->where('user_id', $user->id)
            ->pluck('course_id')
            ->all();

        $userCredentialNames = ExternalCredential::withoutTenantScope()
            ->where('user_id', $user->id)
            ->where('status', 'verified')
            ->pluck('credential_name')
            ->map(fn($n) => strtolower(trim($n)))
            ->all();

        // Resolve course names for display
        $allCourseIds = $path->stages->pluck('required_courses')->flatten()->unique()->filter();
        $courses = Course::withoutTenantScope()
            ->whereIn('id', $allCourseIds)
            ->get(['id', 'title'])
            ->keyBy('id');

        // Calculate stage completion
        $stageProgress = [];
        foreach ($path->stages as $stage) {
            $reqCourses = $stage->required_courses ?? [];
            $reqCerts = $stage->required_certifications ?? [];

            $courseDone = empty($reqCourses) || collect($reqCourses)->every(fn($cid) => in_array($cid, $completedCourseIds));

            $certDone = empty($reqCerts) || collect($reqCerts)->every(function ($cert) use ($userCredentialNames) {
                return in_array(strtolower(trim($cert)), $userCredentialNames);
            });

            $stageProgress[$stage->id] = [
                'course_done' => $courseDone,
                'cert_done' => $certDone,
                'complete' => $courseDone && $certDone,
            ];
        }

        return view('employee.career-paths.show', compact('path', 'enrollment', 'stageProgress', 'courses'));
    }

    public function enroll(int $id)
    {
        $user = Auth::user();

        $path = CareerPath::withoutTenantScope()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->with('stages')
            ->findOrFail($id);

        $firstStage = $path->stages->first();

        CareerPathEnrollment::firstOrCreate(
            ['user_id' => $user->id, 'career_path_id' => $path->id],
            [
                'current_stage_id' => $firstStage?->id,
                'enrolled_at' => now(),
            ]
        );

        return redirect()->route('learn.career-paths.show', $path->id)
            ->with('success', 'You have enrolled in this career path.');
    }
}
