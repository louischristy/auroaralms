<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\PhishingCampaign;
use App\Models\PhishingResult;
use App\Models\PhishingTemplate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhishingController extends Controller
{
    public function index()
    {
        $campaigns = PhishingCampaign::with('template', 'creator')
            ->withCount('results')
            ->withCount(['results as sent_count' => fn ($q) => $q])
            ->withCount(['results as opened_count' => fn ($q) => $q->whereIn('status', ['opened', 'clicked', 'submitted', 'reported'])])
            ->withCount(['results as clicked_count' => fn ($q) => $q->whereIn('status', ['clicked', 'submitted'])])
            ->withCount(['results as reported_count' => fn ($q) => $q->where('status', 'reported')])
            ->latest()
            ->paginate(20);

        return view('client.phishing.index', compact('campaigns'));
    }

    public function create()
    {
        $templates = PhishingTemplate::where('is_system', true)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('client.phishing.create', compact('templates', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'template_id' => ['required', 'exists:phishing_templates,id'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'target' => ['required', 'in:all,departments'],
            'department_ids' => ['required_if:target,departments', 'array'],
            'department_ids.*' => ['exists:departments,id'],
        ]);

        $campaign = PhishingCampaign::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'template_id' => $validated['template_id'],
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('manage.phishing.show', $campaign)
            ->with('success', 'Phishing campaign created successfully.');
    }

    public function show(PhishingCampaign $campaign)
    {
        $campaign->load('template', 'creator');

        $results = $campaign->results()->with('user')->latest()->get();

        $statusCounts = [
            'sent' => $results->where('status', 'sent')->count(),
            'opened' => $results->where('status', 'opened')->count(),
            'clicked' => $results->where('status', 'clicked')->count(),
            'submitted' => $results->where('status', 'submitted')->count(),
            'reported' => $results->where('status', 'reported')->count(),
        ];

        $total = $results->count();

        return view('client.phishing.show', compact('campaign', 'results', 'statusCounts', 'total'));
    }

    public function simulate(PhishingCampaign $campaign)
    {
        // Delete existing results for re-simulation
        $campaign->results()->delete();

        // Get users in the tenant
        $users = User::where('tenant_id', Auth::user()->tenant_id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'))
            ->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'No employee users found in your organization to simulate.');
        }

        $now = now();

        foreach ($users as $user) {
            $rand = mt_rand(1, 100);

            if ($rand <= 15) {
                // 15% reported
                $status = 'reported';
                $opened = $now->copy()->addMinutes(mt_rand(1, 120));
                $reported = $opened->copy()->addMinutes(mt_rand(1, 30));
                $data = [
                    'email_sent_at' => $now,
                    'email_opened_at' => $opened,
                    'reported_at' => $reported,
                    'status' => 'reported',
                ];
            } elseif ($rand <= 20) {
                // 5% submitted data
                $opened = $now->copy()->addMinutes(mt_rand(1, 120));
                $clicked = $opened->copy()->addMinutes(mt_rand(1, 30));
                $submitted = $clicked->copy()->addMinutes(mt_rand(1, 10));
                $data = [
                    'email_sent_at' => $now,
                    'email_opened_at' => $opened,
                    'link_clicked_at' => $clicked,
                    'data_submitted_at' => $submitted,
                    'status' => 'submitted',
                ];
            } elseif ($rand <= 45) {
                // 25% clicked (making ~30% total with submitted)
                $opened = $now->copy()->addMinutes(mt_rand(1, 120));
                $clicked = $opened->copy()->addMinutes(mt_rand(1, 30));
                $data = [
                    'email_sent_at' => $now,
                    'email_opened_at' => $opened,
                    'link_clicked_at' => $clicked,
                    'status' => 'clicked',
                ];
            } elseif ($rand <= 70) {
                // 25% opened only (making ~70% total opened)
                $opened = $now->copy()->addMinutes(mt_rand(1, 240));
                $data = [
                    'email_sent_at' => $now,
                    'email_opened_at' => $opened,
                    'status' => 'opened',
                ];
            } else {
                // 30% sent only (not opened)
                $data = [
                    'email_sent_at' => $now,
                    'status' => 'sent',
                ];
            }

            PhishingResult::create(array_merge($data, [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'tenant_id' => Auth::user()->tenant_id,
            ]));
        }

        $campaign->update([
            'status' => 'completed',
            'sent_at' => $now,
        ]);

        return redirect()->route('manage.phishing.show', $campaign)
            ->with('success', "Simulation complete! {$users->count()} results generated.");
    }
}
