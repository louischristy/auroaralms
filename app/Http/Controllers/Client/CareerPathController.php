<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CareerPath;
use App\Models\CareerPathEnrollment;
use App\Models\CareerPathStage;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CareerPathController extends Controller
{
    private function tenantId(): ?int
    {
        return app()->bound('current_tenant_id') ? app('current_tenant_id') : null;
    }

    private function requireTenant(): int
    {
        $tenantId = $this->tenantId();
        abort_unless($tenantId, 403, 'Switch to a tenant context to manage career paths.');
        return $tenantId;
    }

    private function availableCourses(int $tenantId)
    {
        return Course::where('is_active', true)
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                  ->orWhereIn('id', DB::table('course_tenant')->where('tenant_id', $tenantId)->select('course_id'));
            })
            ->orderBy('title')
            ->get(['id', 'title', 'category']);
    }

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $paths = CareerPath::withoutTenantScope()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when(!$tenantId, fn($q) => $q->whereNotNull('tenant_id'))
            ->withCount(['stages', 'enrollments'])
            ->when($request->search, fn($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15)->withQueryString();

        return view('client.career-paths.index', compact('paths', 'tenantId'));
    }

    public function create()
    {
        $tenantId = $this->requireTenant();
        $courses = $this->availableCourses($tenantId);

        return view('client.career-paths.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $tenantId = $this->requireTenant();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'stages' => ['nullable', 'array'],
            'stages.*.title' => ['required', 'string', 'max:255'],
            'stages.*.description' => ['nullable', 'string', 'max:2000'],
            'stages.*.required_courses' => ['nullable', 'array'],
            'stages.*.required_courses.*' => ['integer', 'exists:courses,id'],
            'stages.*.required_certifications' => ['nullable', 'array'],
            'stages.*.required_certifications.*' => ['string', 'max:255'],
            'stages.*.estimated_duration_days' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated, $tenantId) {
            $path = CareerPath::create([
                'tenant_id' => $tenantId,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            foreach ($validated['stages'] ?? [] as $i => $stageData) {
                $path->stages()->create([
                    'title' => $stageData['title'],
                    'description' => $stageData['description'] ?? null,
                    'stage_order' => $i,
                    'required_courses' => $stageData['required_courses'] ?? [],
                    'required_certifications' => $stageData['required_certifications'] ?? [],
                    'estimated_duration_days' => $stageData['estimated_duration_days'] ?? null,
                ]);
            }
        });

        return redirect()->route('manage.career-paths.index')
            ->with('success', 'Career path created.');
    }

    public function show(int $id)
    {
        $tenantId = $this->tenantId();

        $path = CareerPath::withoutTenantScope()
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->with(['stages', 'enrollments.user', 'enrollments.currentStage'])
            ->findOrFail($id);

        $courses = Course::withoutTenantScope()
            ->whereIn('id', $path->stages->pluck('required_courses')->flatten()->unique()->filter())
            ->get()->keyBy('id');

        return view('client.career-paths.show', compact('path', 'courses'));
    }

    public function edit(int $id)
    {
        $tenantId = $this->requireTenant();

        $path = CareerPath::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->with('stages')
            ->findOrFail($id);

        $courses = $this->availableCourses($tenantId);

        return view('client.career-paths.edit', compact('path', 'courses'));
    }

    public function update(Request $request, int $id)
    {
        $tenantId = $this->requireTenant();

        $path = CareerPath::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'stages' => ['nullable', 'array'],
            'stages.*.title' => ['required', 'string', 'max:255'],
            'stages.*.description' => ['nullable', 'string', 'max:2000'],
            'stages.*.required_courses' => ['nullable', 'array'],
            'stages.*.required_courses.*' => ['integer', 'exists:courses,id'],
            'stages.*.required_certifications' => ['nullable', 'array'],
            'stages.*.required_certifications.*' => ['string', 'max:255'],
            'stages.*.estimated_duration_days' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($path, $validated) {
            $path->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);

            // Replace stages
            $path->stages()->delete();

            foreach ($validated['stages'] ?? [] as $i => $stageData) {
                $path->stages()->create([
                    'title' => $stageData['title'],
                    'description' => $stageData['description'] ?? null,
                    'stage_order' => $i,
                    'required_courses' => $stageData['required_courses'] ?? [],
                    'required_certifications' => $stageData['required_certifications'] ?? [],
                    'estimated_duration_days' => $stageData['estimated_duration_days'] ?? null,
                ]);
            }
        });

        return redirect()->route('manage.career-paths.index')
            ->with('success', 'Career path updated.');
    }

    public function destroy(int $id)
    {
        $tenantId = $this->requireTenant();

        CareerPath::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->findOrFail($id)
            ->delete();

        return redirect()->route('manage.career-paths.index')
            ->with('success', 'Career path deleted.');
    }
}
