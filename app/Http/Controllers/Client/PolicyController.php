<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::withCount('acknowledgments')->latest()->paginate(15);
        return view('client.policies.index', compact('policies'));
    }

    public function create()
    {
        return view('client.policies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id;
        $validated['created_by'] = Auth::id();
        $validated['version'] = $validated['version'] ?? '1.0';

        Policy::create($validated);

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy created successfully.');
    }

    public function show(Policy $policy)
    {
        $policy->load('acknowledgments.user');
        return view('client.policies.show', compact('policy'));
    }

    public function edit(Policy $policy)
    {
        return view('client.policies.edit', compact('policy'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $policy->update($validated);

        return redirect()->route('manage.policies.show', $policy)
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
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

        $policy->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // TODO: Create policy assignment records and send notifications

        return back()->with('success', 'Policy pushed to users successfully.');
    }
}
