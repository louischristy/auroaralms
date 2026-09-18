<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
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
            'custom_click_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'custom_report_rate' => ['nullable', 'integer', 'min:0', 'max:100'],
            'training_aware' => ['boolean'],
        ]);

        $campaign = PhishingCampaign::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'template_id' => $validated['template_id'],
            'target_type' => $validated['target'],
            'target_department_ids' => $validated['target'] === 'departments' ? ($validated['department_ids'] ?? []) : null,
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'status' => $validated['scheduled_at'] ? 'scheduled' : 'draft',
            'custom_click_rate' => $validated['custom_click_rate'] ?? null,
            'custom_report_rate' => $validated['custom_report_rate'] ?? null,
            'training_aware' => $request->boolean('training_aware', true),
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

        $tenantId = Auth::user()->tenant_id;
        $campaign->load('template');

        // Get target users — respect department targeting
        $usersQuery = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('name', 'employee'));

        if ($campaign->target_type === 'departments' && !empty($campaign->target_department_ids)) {
            $usersQuery->whereIn('department_id', $campaign->target_department_ids);
        }

        $users = $usersQuery->get();

        if ($users->isEmpty()) {
            return back()->with('error', 'No employee users found matching the target criteria.');
        }

        // Determine base rates from template difficulty
        $difficulty = $campaign->template->difficulty ?? 'medium';
        $baseRates = $this->getBaseRates($difficulty);

        // Override with custom rates if set
        if ($campaign->custom_click_rate !== null) {
            $baseRates = $this->adjustRatesForClickRate($baseRates, $campaign->custom_click_rate);
        }
        if ($campaign->custom_report_rate !== null) {
            $baseRates['reported'] = $campaign->custom_report_rate;
        }

        // If training-aware, get users who completed phishing-related courses
        $trainedUserIds = [];
        if ($campaign->training_aware) {
            $trainedUserIds = CourseEnrollment::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereHas('course', fn ($q) => $q->whereIn('category', [
                    'Phishing & Email Security',
                    'Social Engineering',
                ]))
                ->pluck('user_id')
                ->unique()
                ->toArray();
        }

        $now = now();

        foreach ($users as $user) {
            $rates = $baseRates;

            // Trained users get better rates: 40% less likely to click/submit, 50% more likely to report
            if ($campaign->training_aware && in_array($user->id, $trainedUserIds)) {
                $rates['clicked'] = (int) round($rates['clicked'] * 0.6);
                $rates['submitted'] = (int) round($rates['submitted'] * 0.6);
                $rates['reported'] = min(100, (int) round($rates['reported'] * 1.5));
            }

            $data = $this->generateResult($rates, $now);

            PhishingResult::create(array_merge($data, [
                'campaign_id' => $campaign->id,
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
            ]));
        }

        $campaign->update([
            'status' => 'completed',
            'sent_at' => $now,
        ]);

        $trainedCount = $campaign->training_aware ? count(array_intersect($users->pluck('id')->toArray(), $trainedUserIds)) : 0;
        $msg = "Simulation complete! {$users->count()} results generated.";
        if ($trainedCount > 0) {
            $msg .= " ({$trainedCount} users had phishing training — their results reflect improved awareness.)";
        }

        return redirect()->route('manage.phishing.show', $campaign)
            ->with('success', $msg);
    }

    /**
     * Base probability rates by template difficulty.
     * Values represent approximate percentages for each outcome bucket.
     */
    private function getBaseRates(string $difficulty): array
    {
        return match ($difficulty) {
            'easy' => [
                'not_opened' => 40, // easy to spot → many ignore
                'opened' => 30,     // opened but didn't click
                'clicked' => 15,    // clicked link
                'submitted' => 3,   // submitted credentials
                'reported' => 12,   // reported as phishing
            ],
            'hard' => [
                'not_opened' => 15, // hard to spot → most open
                'opened' => 25,
                'clicked' => 30,
                'submitted' => 12,
                'reported' => 18,
            ],
            default => [ // medium
                'not_opened' => 30,
                'opened' => 25,
                'clicked' => 25,
                'submitted' => 5,
                'reported' => 15,
            ],
        };
    }

    /**
     * Adjust rates when a custom click rate is specified.
     */
    private function adjustRatesForClickRate(array $rates, int $clickRate): array
    {
        $totalClick = $clickRate; // clicked + submitted combined
        $submitPortion = max(1, (int) round($totalClick * 0.15)); // ~15% of clickers submit data
        $clickPortion = $totalClick - $submitPortion;

        $remaining = 100 - $totalClick - $rates['reported'];
        $notOpened = (int) round($remaining * 0.55);
        $opened = $remaining - $notOpened;

        return [
            'not_opened' => max(0, $notOpened),
            'opened' => max(0, $opened),
            'clicked' => max(0, $clickPortion),
            'submitted' => max(0, $submitPortion),
            'reported' => $rates['reported'],
        ];
    }

    /**
     * Generate a single user's simulated result with realistic timestamps.
     */
    private function generateResult(array $rates, $sentAt): array
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;

        // reported
        $cumulative += $rates['reported'];
        if ($rand <= $cumulative) {
            $opened = $sentAt->copy()->addMinutes(mt_rand(5, 180));
            return [
                'email_sent_at' => $sentAt,
                'email_opened_at' => $opened,
                'reported_at' => $opened->copy()->addMinutes(mt_rand(1, 45)),
                'status' => 'reported',
            ];
        }

        // submitted
        $cumulative += $rates['submitted'];
        if ($rand <= $cumulative) {
            $opened = $sentAt->copy()->addMinutes(mt_rand(3, 120));
            $clicked = $opened->copy()->addMinutes(mt_rand(1, 15));
            return [
                'email_sent_at' => $sentAt,
                'email_opened_at' => $opened,
                'link_clicked_at' => $clicked,
                'data_submitted_at' => $clicked->copy()->addMinutes(mt_rand(1, 10)),
                'status' => 'submitted',
            ];
        }

        // clicked
        $cumulative += $rates['clicked'];
        if ($rand <= $cumulative) {
            $opened = $sentAt->copy()->addMinutes(mt_rand(5, 180));
            return [
                'email_sent_at' => $sentAt,
                'email_opened_at' => $opened,
                'link_clicked_at' => $opened->copy()->addMinutes(mt_rand(1, 20)),
                'status' => 'clicked',
            ];
        }

        // opened
        $cumulative += $rates['opened'];
        if ($rand <= $cumulative) {
            return [
                'email_sent_at' => $sentAt,
                'email_opened_at' => $sentAt->copy()->addMinutes(mt_rand(10, 480)),
                'status' => 'opened',
            ];
        }

        // not opened
        return [
            'email_sent_at' => $sentAt,
            'status' => 'sent',
        ];
    }
}
