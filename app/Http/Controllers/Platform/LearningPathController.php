<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Concerns\ManagesLearningPathCourses;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LearningPath;
use App\Models\Tenant;
use Illuminate\Http\Request;

class LearningPathController extends Controller
{
    use ManagesLearningPathCourses;

    private function find(int $id): LearningPath
    {
        return LearningPath::withoutTenantScope()->whereNull('tenant_id')->findOrFail($id);
    }

    private function platformCourses()
    {
        return Course::whereNull('tenant_id')->where('is_active', true)
            ->orderBy('category')->orderBy('title')
            ->get(['id', 'title', 'category', 'duration_minutes']);
    }

    public function index(Request $request)
    {
        $paths = LearningPath::withoutTenantScope()->whereNull('tenant_id')
            ->withCount(['courses', 'enrollments', 'tenants'])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->difficulty, fn($q, $d) => $q->where('difficulty', $d))
            ->orderBy('sort_order')->orderBy('title')
            ->paginate(15)->withQueryString();

        return view('platform.learning-paths.index', compact('paths'));
    }

    public function create()
    {
        return view('platform.learning-paths.create', [
            'availableCourses' => $this->platformCourses(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->pathRules());

        $path = new LearningPath($this->pathAttributes($request, $validated));
        $path->tenant_id = null;
        $path->slug = $this->uniqueSlug($validated['title']);
        $path->save();

        $this->syncPathCourses($path, $validated['course_ids'], $this->platformCourses()->pluck('id'));

        return redirect()->route('platform.learning-paths.edit', $path->id)
            ->with('success', 'Learning path created. You can now assign it to tenants.');
    }

    public function edit(int $id)
    {
        $path = $this->find($id)->load('courses');
        $availableCourses = $this->platformCourses();
        $tenants = Tenant::orderBy('name')->get();
        $assignedTenantIds = $path->tenants()->pluck('tenants.id')->toArray();

        return view('platform.learning-paths.edit', [
            'path' => $path,
            'availableCourses' => $availableCourses->concat(
                // keep already-attached courses selectable even if now inactive
                $path->courses->whereNotIn('id', $availableCourses->pluck('id'))
            ),
            'selected' => $this->selectedCoursePayload($path),
            'tenants' => $tenants,
            'assignedTenantIds' => $assignedTenantIds,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $path = $this->find($id);
        $validated = $request->validate($this->pathRules());

        $path->update($this->pathAttributes($request, $validated));

        $allowed = $this->platformCourses()->pluck('id')->merge($path->courses()->pluck('courses.id'));
        $this->syncPathCourses($path, $validated['course_ids'], $allowed);

        return redirect()->route('platform.learning-paths.edit', $path->id)
            ->with('success', 'Learning path updated.');
    }

    public function destroy(int $id)
    {
        $this->find($id)->delete();

        return redirect()->route('platform.learning-paths.index')
            ->with('success', 'Learning path deleted.');
    }

    public function assignTenants(Request $request, int $id)
    {
        $path = $this->find($id);
        $validated = $request->validate([
            'tenant_ids' => 'array',
            'tenant_ids.*' => 'exists:tenants,id',
        ]);

        $path->tenants()->sync($validated['tenant_ids'] ?? []);

        // Make the path's courses available to those tenants too.
        $courseIds = $path->courses()->pluck('courses.id');
        foreach ($validated['tenant_ids'] ?? [] as $tenantId) {
            foreach ($courseIds as $courseId) {
                \DB::table('course_tenant')->updateOrInsert(
                    ['course_id' => $courseId, 'tenant_id' => $tenantId],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        return back()->with('success', 'Tenant assignments updated.');
    }
}
