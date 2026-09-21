<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PolicyPushed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();

            $query = Policy::withoutTenantScope()
                ->withCount('acknowledgments')
                ->with('tenant');

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->search) {
                $query->where('title', 'like', "%{$request->search}%");
            }

            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }

            $policies = $query->latest()->paginate(20)->withQueryString();

            return view('client.policies.index', compact('policies', 'tenants'));
        }

        // Client admin / manager sees only their tenant's policies
        $policies = Policy::withCount('acknowledgments')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($request->status === 'draft', fn ($q) => $q->where('is_published', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.policies.index', compact('policies'));
    }

    public function create()
    {
        $user = Auth::user();
        $tenants = null;

        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();
        }

        return view('client.policies.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'document' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'version' => ['nullable', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $user = Auth::user();

        if ($user->hasRole('platform-admin') && !empty($validated['tenant_id'])) {
            $tenantId = $validated['tenant_id'];
        } else {
            $tenantId = $user->tenant_id;
        }

        // Store PDF
        $file = $request->file('document');
        $originalName = $file->getClientOriginalName();
        $path = $file->store('policies', 'public');

        Policy::withoutTenantScope()->create([
            'tenant_id' => $tenantId,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'document_path' => $path,
            'document_original_name' => $originalName,
            'version' => $validated['version'] ?? '1.0',
            'requires_acknowledgment' => $request->boolean('requires_acknowledgment', true),
            'acknowledgment_deadline_days' => $validated['acknowledgment_deadline_days'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy created successfully.');
    }

    public function show(Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->with('acknowledgments.user', 'tenant')->findOrFail($policy->id);
        } else {
            $policy->load('acknowledgments.user');
        }

        // Get tenant user count for publish stats
        $tenantUserCount = 0;
        if ($policy->tenant_id) {
            $tenantUserCount = User::withoutTenantScope()
                ->where('tenant_id', $policy->tenant_id)
                ->where('is_active', true)
                ->count();
        }

        return view('client.policies.show', compact('policy', 'tenantUserCount'));
    }

    public function edit(Policy $policy)
    {
        $tenants = null;

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
            $tenants = Tenant::orderBy('name')->get();
        }

        return view('client.policies.edit', compact('policy', 'tenants'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'version' => ['required', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $updateData = [
            'title' => $validated['title'],
            'content' => $validated['content'] ?? $policy->content,
            'version' => $validated['version'],
            'requires_acknowledgment' => $request->boolean('requires_acknowledgment'),
            'acknowledgment_deadline_days' => $validated['acknowledgment_deadline_days'] ?? null,
        ];

        if (Auth::user()->hasRole('platform-admin') && isset($validated['tenant_id'])) {
            $updateData['tenant_id'] = $validated['tenant_id'];
        }

        // Replace PDF if new one uploaded
        if ($request->hasFile('document')) {
            // Delete old file
            if ($policy->document_path) {
                Storage::disk('public')->delete($policy->document_path);
            }
            $file = $request->file('document');
            $updateData['document_path'] = $file->store('policies', 'public');
            $updateData['document_original_name'] = $file->getClientOriginalName();
        }

        $policy->update($updateData);

        return redirect()->route('manage.policies.show', $policy)
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        // Delete document file
        if ($policy->document_path) {
            Storage::disk('public')->delete($policy->document_path);
        }

        $policy->delete();

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy deleted successfully.');
    }

    /**
     * Serve the policy PDF for viewing (authenticated access).
     */
    public function viewDocument(Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        if (!$policy->document_path || !Storage::disk('public')->exists($policy->document_path)) {
            abort(404, 'Document not found.');
        }

        return response()->file(Storage::disk('public')->path($policy->document_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($policy->document_original_name ?? 'policy.pdf') . '"',
        ]);
    }

    /**
     * Publish policy and notify all tenant users.
     */
    public function push(Request $request, Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Send notifications to all active tenant users
        $tenantId = $policy->tenant_id;
        $users = User::withoutTenantScope()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($users as $user) {
            $user->notify(new PolicyPushed($policy));
        }

        return back()->with('success', "Policy published and pushed to {$users->count()} user(s).");
    }
}
