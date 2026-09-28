<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\ResolvesTenant;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    use ResolvesTenant;

    public const TYPES = ['info', 'warning', 'urgent', 'success'];
    public const AUDIENCES = ['all', 'employees', 'managers', 'admins'];

    public function index(Request $request)
    {
        $announcements = Announcement::with(['tenant', 'department', 'creator'])
            ->withCount('reads')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when(in_array($request->type, self::TYPES, true), fn ($q) => $q->where('type', $request->type))
            ->when($request->status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($request->status === 'draft', fn ($q) => $q->where('is_published', false))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.announcements.index', [
            'announcements' => $announcements,
            'types' => self::TYPES,
            'showTenant' => $this->isPlatformAdmin(),
        ]);
    }

    public function create()
    {
        return view('client.announcements.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tenantId = $this->resolveTenantId($request->integer('tenant_id') ?: null);
        $this->assertDepartmentInTenant($data['target_department_id'] ?? null, $tenantId);

        $published = $request->boolean('is_published');

        Announcement::withoutTenantScope()->create($data + [
            'tenant_id' => $tenantId,
            'created_by' => Auth::id(),
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => $published,
            'published_at' => $published ? now() : null,
        ]);

        return redirect()->route('manage.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);

        return view('client.announcements.edit', $this->formData($announcement) + compact('announcement'));
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);
        $data = $this->validated($request);
        $this->assertDepartmentInTenant($data['target_department_id'] ?? null, $announcement->tenant_id);

        $published = $request->boolean('is_published');

        $announcement->update($data + [
            'is_pinned' => $request->boolean('is_pinned'),
            'is_published' => $published,
            // Keep original publish date unless newly published
            'published_at' => $published ? ($announcement->published_at ?? now()) : $announcement->published_at,
        ]);

        return redirect()->route('manage.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    public function destroy($id)
    {
        Announcement::findOrFail($id)->delete();

        return redirect()->route('manage.announcements.index')
            ->with('success', 'Announcement deleted.');
    }

    public function publish($id)
    {
        $announcement = Announcement::findOrFail($id);
        $publishing = !$announcement->is_published;

        $announcement->update([
            'is_published' => $publishing,
            'published_at' => $publishing ? now() : $announcement->published_at,
        ]);

        return back()->with('success', $publishing ? 'Announcement published.' : 'Announcement unpublished.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:10000'],
            'type' => ['required', Rule::in(self::TYPES)],
            'target_audience' => ['required', Rule::in(self::AUDIENCES)],
            'target_department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'expires_at' => ['nullable', 'date'],
            'tenant_id' => [$this->isPlatformAdmin() ? 'required' : 'nullable', 'integer', 'exists:tenants,id'],
        ]);

        unset($data['tenant_id']);

        // Date picker gives a day; the announcement stays live through the end of it
        $data['expires_at'] = !empty($data['expires_at']) ? Carbon::parse($data['expires_at'])->endOfDay() : null;

        return $data;
    }

    private function assertDepartmentInTenant(?int $departmentId, ?int $tenantId): void
    {
        if ($departmentId && !Department::withoutTenantScope()->where('id', $departmentId)->where('tenant_id', $tenantId)->exists()) {
            abort(422, 'The selected department does not belong to this organization.');
        }
    }

    private function formData(?Announcement $announcement = null): array
    {
        $departments = $this->isPlatformAdmin()
            ? Department::withoutTenantScope()->with('tenant')->orderBy('name')->get()
            : Department::orderBy('name')->get();

        return [
            'tenants' => $announcement ? null : $this->tenantOptions(),
            'departments' => $departments,
            'types' => self::TYPES,
            'audiences' => self::AUDIENCES,
        ];
    }
}
