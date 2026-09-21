<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\PolicyAcknowledgment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PolicyAcknowledgmentController extends Controller
{
    public function index()
    {
        $policies = Policy::where('is_published', true)
            ->latest('published_at')
            ->paginate(15);

        return view('employee.policies.index', compact('policies'));
    }

    public function show(Policy $policy)
    {
        $acknowledged = $policy->acknowledgedByUser(Auth::user());
        return view('employee.policies.show', compact('policy', 'acknowledged'));
    }

    public function acknowledge(Request $request, Policy $policy)
    {
        if ($policy->acknowledgedByUser(Auth::user())) {
            return back()->with('info', 'You have already acknowledged this policy.');
        }

        PolicyAcknowledgment::create([
            'tenant_id' => Auth::user()->tenant_id,
            'policy_id' => $policy->id,
            'user_id' => Auth::id(),
            'version' => $policy->version,
            'acknowledged_at' => now(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Policy acknowledged successfully.');
    }

    public function viewDocument(Policy $policy)
    {
        if (!$policy->is_published) {
            abort(404);
        }

        if (!$policy->document_path || !Storage::disk('public')->exists($policy->document_path)) {
            abort(404, 'Document not found.');
        }

        return response()->file(Storage::disk('public')->path($policy->document_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($policy->document_original_name ?? 'policy.pdf') . '"',
        ]);
    }
}
