<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get courses the user is enrolled in
        $enrolledCourseIds = CourseEnrollment::withoutTenantScope()
            ->where('user_id', $user->id)
            ->pluck('course_id');

        $assignments = Assignment::withoutTenantScope()
            ->whereIn('course_id', $enrolledCourseIds)
            ->where('is_active', true)
            ->with(['course', 'submissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->latest('submitted_at');
            }])
            ->orderBy('course_id')
            ->orderBy('sort_order')
            ->get();

        return view('employee.assignments.index', compact('assignments'));
    }

    public function show(Assignment $assignment)
    {
        $user = Auth::user();
        $this->authorizeEnrolled($assignment, $user->id);

        $assignment->load('course');
        $submission = $assignment->submissions()
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        return view('employee.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $user = Auth::user();
        $this->authorizeEnrolled($assignment, $user->id);

        // Check if already approved
        $existing = $assignment->submissions()
            ->where('user_id', $user->id)
            ->where('status', AssignmentSubmission::STATUS_APPROVED)
            ->exists();

        if ($existing) {
            return back()->with('error', 'Your submission has already been approved.');
        }

        $allowedTypes = $assignment->allowedFileTypesArray();
        $maxSize = $assignment->max_file_size_mb * 1024; // KB for validation

        $request->validate([
            'file' => "required|file|max:{$maxSize}|mimes:" . implode(',', $allowedTypes),
            'notes' => 'nullable|string|max:2000',
        ]);

        $file = $request->file('file');
        $path = $file->store('assignments/' . $assignment->id, 'local');

        // Determine status: resubmitted if a rejected submission exists
        $wasRejected = $assignment->submissions()
            ->where('user_id', $user->id)
            ->where('status', AssignmentSubmission::STATUS_REJECTED)
            ->exists();

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'notes' => $request->notes,
            'status' => $wasRejected ? AssignmentSubmission::STATUS_RESUBMITTED : AssignmentSubmission::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return redirect()->route('learn.assignments.my-submission', $assignment)
            ->with('success', 'Assignment submitted successfully.');
    }

    public function mySubmission(Assignment $assignment)
    {
        $user = Auth::user();
        $this->authorizeEnrolled($assignment, $user->id);

        $assignment->load('course');
        $submission = $assignment->submissions()
            ->where('user_id', $user->id)
            ->with('reviewer')
            ->latest('submitted_at')
            ->first();

        if (!$submission) {
            return redirect()->route('learn.assignments.show', $assignment)
                ->with('error', 'You have not submitted this assignment yet.');
        }

        return view('employee.assignments.submission', compact('assignment', 'submission'));
    }

    private function authorizeEnrolled(Assignment $assignment, int $userId): void
    {
        $enrolled = CourseEnrollment::withoutTenantScope()
            ->where('user_id', $userId)
            ->where('course_id', $assignment->course_id)
            ->exists();

        if (!$enrolled) {
            abort(Response::HTTP_FORBIDDEN, 'You are not enrolled in this course.');
        }
    }
}
