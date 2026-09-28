<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\ManagesLearningPathCourses;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Department;
use App\Models\LearningPath;
use App\Models\LearningPathEnrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientLearningPathController extends Controller
{
    use ManagesLearningPathCourses;

    private function tenantId(): ?int
    {
        return app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
    }

    private function requireTenant(): int
    {
        $tenantId = $this->tenantId();
        abort_unless($tenantId, 403, 'Switch to a tenant context to manage tenant learning paths.');
        return $tenantId;
    }

    /** Paths visible to the tenant: its own + platform paths assigned to it. */
    private function visibleQuery(?int $tenantId)
    {
        $q = LearningPath::withoutTenantScope();
        if (!$tenantId) {
            return $q->whereNotNull('tenant_id');
        }
        return $q->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhere(fn($q) => $q->whereNull('tenant_id')
                  ->whereIn('id', DB::table('learning_path_tenant')
                      ->where('tenant_id', $tenantId)->select('learning_path_id')));
        });
    }

    private function findVisible(int $id): LearningPath
    {
        return $this->visibleQuery($this->tenantId())->findOrFail($id);
    }

    private function findOwn(int $id): LearningPath
    {
        $tenantId = $this->requireTenant();
        return LearningPath::withoutTenantScope()->where('tenant_id', $tenantId)->findOrFail($id);
    }

    /** Courses a tenant may put in a path: its own + assigned platform courses. */
    private function availableCourses(int $tenantId)
    {
        return Course::where('is_active', true)
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereIn('id', DB::table('course_tenant')->where('tenant_id', $tenantId)->select('course_id'));
            })
            ->orderBy('category')->orderBy('title')
            ->get(['id', 'title', 'category', 'duration_minutes']);
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $paths = $this->visibleQuery($tenantId)
            ->with('tenant:id,name')
            ->withCount(['courses', 'enrollments'])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->difficulty, fn($q, $d) => $q->where('difficulty', $d))
            ->when($request->source === 'platform', fn($q) => $q->whereNull('tenant_id'))
            ->when($request->source === 'own', fn($q) => $q->whereNotNull('tenant_id'))
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        return view('client.learning-paths.index', compact('paths', 'tenantId'));
    }

    public function create()
    {
        $tenantId = $this->requireTenant();

        return view('client.learning-paths.create', [
            'availableCourses' => $this->availableCourses($tenantId),
        ]);
    }

    public function store(Request $request)
    {
        $tenantId = $this->requireTenant();
        $validated = $request->validate($this->pathRules());

        $path = new LearningPath($this->pathAttributes($request, $validated));
        $path->tenant_id = $tenantId;
        $path->slug = $this->uniqueSlug($validated['title']);
        $path->save();

        $this->syncPathCourses($path, $validated['course_ids'], $this->availableCourses($tenantId)->pluck('id'));

        return redirect()->route('manage.learning-paths.index')
            ->with('success', 'Learning path created.');
    }

    public function edit(int $id)
    {
        $tenantId = $this->requireTenant();
        $path = $this->findOwn($id)->load('courses');
        $available = $this->availableCourses($tenantId);

        return view('client.learning-paths.edit', [
            'path' => $path,
            'availableCourses' => $available->concat($path->courses->whereNotIn('id', $available->pluck('id'))),
            'selected' => $this->selectedCoursePayload($path),
        ]);
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->requireTenant();
        $path = $this->findOwn($id);
        $validated = $request->validate($this->pathRules());

        $path->update($this->pathAttributes($request, $validated));

        $allowed = $this->availableCourses($tenantId)->pluck('id')->merge($path->courses()->pluck('courses.id'));
        $this->syncPathCourses($path, $validated['course_ids'], $allowed);

        return redirect()->route('manage.learning-paths.index')
            ->with('success', 'Learning path updated.');
    }

    public function destroy(int $id)
    {
        $this->findOwn($id)->delete();

        return redirect()->route('manage.learning-paths.index')
            ->with('success', 'Learning path deleted.');
    }

    public function assignUsers(int $id)
    {
        $tenantId = $this->tenantId();
        $path = $this->findVisible($id)->load('courses');

        if ($tenantId) {
            $users = User::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('name')->get();
            $departments = Department::where('tenant_id', $tenantId)->orderBy('name')->get();
        } else {
            $users = User::withoutTenantScope()->whereNotNull('tenant_id')->where('is_active', true)->with('tenant')->orderBy('name')->get();
            $departments = Department::withoutTenantScope()->with('tenant')->orderBy('name')->get();
        }

        $enrollments = LearningPathEnrollment::withoutTenantScope()
            ->where('learning_path_id', $path->id)->get();
        $assignedUserIds = $enrollments->pluck('user_id')->all();
        $dueDate = optional($enrollments->first(fn($e) => $e->due_date))->due_date;

        return view('client.learning-paths.assign-users', compact('path', 'users', 'departments', 'assignedUserIds', 'dueDate'));
    }

    public function assignUsersSave(Request $request, int $id)
    {
        $tenantId = $this->tenantId();
        $path = $this->findVisible($id)->load('courses');

        $validated = $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        $userQuery = $tenantId
            ? User::where('tenant_id', $tenantId)
            : User::withoutTenantScope()->whereNotNull('tenant_id');
        $userQuery->where('is_active', true);

        $userIds = collect($validated['user_ids'] ?? []);
        if (!empty($validated['department_ids'])) {
            $userIds = $userIds->merge(
                (clone $userQuery)->whereIn('department_id', $validated['department_ids'])->pluck('id')
            );
        }
        // Restrict to users the acting admin is allowed to assign.
        $users = (clone $userQuery)->whereIn('id', $userIds->unique())->get(['id', 'tenant_id']);
        $due = $validated['due_date'] ?? null;

        DB::transaction(function () use ($users, $path, $due, $tenantId) {
            $courseIds = $path->courses->pluck('id');

            foreach ($users as $user) {
                $enrollment = LearningPathEnrollment::withoutTenantScope()->firstOrNew([
                    'user_id' => $user->id,
                    'learning_path_id' => $path->id,
                ]);
                $enrollment->tenant_id = $user->tenant_id;
                $enrollment->due_date = $due ?? $enrollment->due_date;
                $enrollment->status = $enrollment->status ?: 'not_started';
                $enrollment->save();

                // Ensure the user can access and is enrolled in each course.
                foreach ($courseIds as $cid) {
                    $exists = DB::table('course_user')->where('course_id', $cid)->where('user_id', $user->id)->exists();
                    if (!$exists) {
                        DB::table('course_user')->insert([
                            'course_id' => $cid, 'user_id' => $user->id, 'assigned_by' => Auth::id(),
                            'due_date' => $due, 'is_mandatory' => $path->is_mandatory,
                            'created_at' => now(), 'updated_at' => now(),
                        ]);
                    }
                }
                foreach ($courseIds as $cid) {
                    CourseEnrollment::withoutTenantScope()->firstOrCreate(
                        ['user_id' => $user->id, 'course_id' => $cid],
                        ['tenant_id' => $user->tenant_id, 'status' => 'not_started', 'due_date' => $due]
                    );
                }
                $enrollment->recalculateProgress();
            }

            // Remove untouched enrollments for users that were deselected.
            $stale = LearningPathEnrollment::withoutTenantScope()
                ->where('learning_path_id', $path->id)
                ->where('status', 'not_started')
                ->whereNotIn('user_id', $users->pluck('id'))
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId));
            $stale->delete();
        });

        return back()->with('success', "Learning path assigned to {$users->count()} user(s).");
    }
}
