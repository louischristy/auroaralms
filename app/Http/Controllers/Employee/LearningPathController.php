<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\LearningPath;
use App\Models\LearningPathEnrollment;
use Illuminate\Support\Facades\Auth;

class LearningPathController extends Controller
{
    public function index()
    {
        $enrollments = LearningPathEnrollment::withoutTenantScope()
            ->where('user_id', Auth::id())
            ->with(['learningPath' => fn($q) => $q->withoutTenantScope()->withCount('courses')])
            ->latest()
            ->get()
            ->filter(fn($e) => $e->learningPath && $e->learningPath->is_active);

        foreach ($enrollments as $enrollment) {
            $enrollment->recalculateProgress();
        }

        return view('employee.learning-paths.index', compact('enrollments'));
    }

    public function show(int $id)
    {
        $user = Auth::user();

        $enrollment = LearningPathEnrollment::withoutTenantScope()
            ->where('user_id', $user->id)
            ->where('learning_path_id', $id)
            ->firstOrFail();

        $path = LearningPath::withoutTenantScope()->where('is_active', true)->findOrFail($id);
        $path->load('courses');

        $courseEnrollments = CourseEnrollment::withoutTenantScope()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $path->courses->pluck('id'))
            ->get()->keyBy('course_id');

        $steps = [];
        $previousDone = true;
        $currentAssigned = false;

        foreach ($path->courses as $course) {
            $ce = $courseEnrollments[$course->id] ?? null;
            $completed = $ce && $ce->status === 'completed';
            $progress = $completed ? 100 : (int) round($ce->progress_percent ?? 0);

            $locked = false;
            $lockReason = null;
            if ($path->is_sequential && !$previousDone) {
                $locked = true;
                $lockReason = 'Complete the previous course to unlock.';
            }
            $days = $course->pivot->unlock_after_days;
            if (!$locked && $days) {
                $unlockAt = $enrollment->created_at->copy()->addDays($days);
                if ($unlockAt->isFuture()) {
                    $locked = true;
                    $lockReason = 'Unlocks on ' . $unlockAt->format('M j, Y') . '.';
                }
            }

            $isCurrent = !$completed && !$locked && !$currentAssigned;
            if ($isCurrent) {
                $currentAssigned = true;
            }

            $steps[] = [
                'course' => $course,
                'completed' => $completed,
                'locked' => $locked,
                'current' => $isCurrent,
                'progress' => $progress,
                'started' => $progress > 0 || ($ce && $ce->status === 'in_progress'),
                'lock_reason' => $lockReason,
                'required' => (bool) $course->pivot->is_required,
            ];

            // Only required courses gate the following ones.
            if ($course->pivot->is_required) {
                $previousDone = $previousDone && $completed;
            }
        }

        $enrollment->recalculateProgress();

        return view('employee.learning-paths.show', compact('path', 'enrollment', 'steps'));
    }
}
