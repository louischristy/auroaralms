<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AssignmentController extends Controller
{
    private function tenantId(): ?int
    {
        return app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
    }

    private function authorizeTenantAssignment(Assignment $assignment): void
    {
        $tenantId = $this->tenantId();
        if (!$tenantId) {
            return;
        }
        if ($assignment->tenant_id && (int) $assignment->tenant_id === $tenantId) {
            return;
        }
        abort(Response::HTTP_FORBIDDEN, 'This assignment does not belong to your organization.');
    }

    private function authorizeTenantCourse(Course $course): void
    {
        $tenantId = $this->tenantId();
        if (!$tenantId) {
            return;
        }
        if ($course->tenant_id && (int) $course->tenant_id === $tenantId) {
            return;
        }
        abort(Response::HTTP_FORBIDDEN, 'This course does not belong to your organization.');
    }

    public function index()
    {
        $tenantId = $this->tenantId();

        if (!$tenantId) {
            $assignments = Assignment::withoutTenantScope()
                ->with(['course', 'submissions'])
                ->orderBy('course_id')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('course_id');
            $courses = Course::withoutTenantScope()->whereIn('id', $assignments->keys())->get()->keyBy('id');
        } else {
            $assignments = Assignment::with(['course', 'submissions'])
                ->orderBy('course_id')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('course_id');
            $courses = Course::withoutTenantScope()->whereIn('id', $assignments->keys())->get()->keyBy('id');
        }

        return view('client.assignments.index', compact('assignments', 'courses'));
    }

    public function create(Course $course)
    {
        $this->authorizeTenantCourse($course);

        return view('client.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeTenantCourse($course);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'instructions' => 'nullable|string|max:10000',
            'due_days_after_enrollment' => 'nullable|integer|min:1',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|string|max:255',
            'is_mandatory' => 'boolean',
        ]);

        $validated['is_mandatory'] = $request->boolean('is_mandatory');
        $validated['tenant_id'] = $this->tenantId();
        $validated['sort_order'] = $course->assignments()->max('sort_order') + 1;

        $course->assignments()->create($validated);

        return redirect()->route('manage.assignments.index')
            ->with('success', 'Assignment created successfully.');
    }

    public function edit(Assignment $assignment)
    {
        $this->authorizeTenantAssignment($assignment);
        $assignment->load('course');

        return view('client.assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $this->authorizeTenantAssignment($assignment);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'instructions' => 'nullable|string|max:10000',
            'due_days_after_enrollment' => 'nullable|integer|min:1',
            'max_file_size_mb' => 'required|integer|min:1|max:100',
            'allowed_file_types' => 'required|string|max:255',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $validated['is_mandatory'] = $request->boolean('is_mandatory');
        $validated['is_active'] = $request->boolean('is_active');

        $assignment->update($validated);

        return back()->with('success', 'Assignment updated successfully.');
    }

    public function submissions(Assignment $assignment)
    {
        $this->authorizeTenantAssignment($assignment);
        $assignment->load('course');

        $submissions = $assignment->submissions()
            ->with('user', 'reviewer')
            ->orderByDesc('submitted_at')
            ->paginate(20);

        return view('client.assignments.submissions', compact('assignment', 'submissions'));
    }

    public function review(AssignmentSubmission $submission)
    {
        $submission->load(['assignment.course', 'user', 'reviewer']);
        $this->authorizeTenantAssignment($submission->assignment);

        return view('client.assignments.review', compact('submission'));
    }

    public function submitReview(Request $request, AssignmentSubmission $submission)
    {
        $submission->load('assignment');
        $this->authorizeTenantAssignment($submission->assignment);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'reviewer_feedback' => 'nullable|string|max:5000',
            'grade' => 'nullable|integer|min:0|max:100',
        ]);

        $submission->update([
            'status' => $validated['status'],
            'reviewer_feedback' => $validated['reviewer_feedback'],
            'grade' => $validated['grade'],
            'reviewer_id' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('manage.assignments.submissions', $submission->assignment)
            ->with('success', 'Submission reviewed successfully.');
    }
}
