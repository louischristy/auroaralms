<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('platform-admin')) {
            // Platform admin sees all policies grouped by tenant
            $tenants = Tenant::withoutTenantScope()->orderBy('name')->get();

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
            $tenants = Tenant::withoutTenantScope()->orderBy('name')->get();
        }

        return view('client.policies.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $user = Auth::user();

        // Platform admin can assign to any tenant
        if ($user->hasRole('platform-admin') && !empty($validated['tenant_id'])) {
            $validated['tenant_id'] = $validated['tenant_id'];
        } else {
            $validated['tenant_id'] = $user->tenant_id;
        }

        $validated['created_by'] = Auth::id();
        $validated['version'] = $validated['version'] ?? '1.0';

        Policy::withoutTenantScope()->create($validated);

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy created successfully.');
    }

    public function show(Policy $policy)
    {
        // Platform admin can view any policy
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->with('acknowledgments.user', 'tenant')->findOrFail($policy->id);
        } else {
            $policy->load('acknowledgments.user');
        }

        return view('client.policies.show', compact('policy'));
    }

    public function edit(Policy $policy)
    {
        $tenants = null;

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
            $tenants = Tenant::withoutTenantScope()->orderBy('name')->get();
        }

        return view('client.policies.edit', compact('policy', 'tenants'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->update($validated);

        return redirect()->route('manage.policies.show', $policy)
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->delete();

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy deleted successfully.');
    }

    public function push(Request $request, Policy $policy)
    {
        $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'push_to_all' => ['boolean'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // TODO: Create policy assignment records and send notifications

        return back()->with('success', 'Policy pushed to users successfully.');
    }
}
