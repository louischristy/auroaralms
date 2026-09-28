<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Concerns\ResolvesTenant;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\VirtualClassroom;
use App\Models\VirtualClassroomAttendee;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VirtualClassroomController extends Controller
{
    use ResolvesTenant;

    public const PLATFORMS = ['zoom' => 'Zoom', 'teams' => 'Microsoft Teams', 'meet' => 'Google Meet', 'webex' => 'Webex', 'other' => 'Other'];
    public const STATUSES = ['scheduled', 'live', 'completed', 'cancelled'];
    public const RECURRENCE = ['daily', 'weekly', 'monthly'];
    public const TIMEZONES = [
        'Asia/Kuala_Lumpur' => 'Kuala Lumpur (GMT+8)',
        'Asia/Singapore' => 'Singapore (GMT+8)',
        'Asia/Jakarta' => 'Jakarta (GMT+7)',
        'Asia/Bangkok' => 'Bangkok (GMT+7)',
        'Asia/Manila' => 'Manila (GMT+8)',
        'Asia/Hong_Kong' => 'Hong Kong (GMT+8)',
        'Asia/Tokyo' => 'Tokyo (GMT+9)',
        'Asia/Kolkata' => 'India (GMT+5:30)',
        'Asia/Dubai' => 'Dubai (GMT+4)',
        'Australia/Sydney' => 'Sydney',
        'Europe/London' => 'London',
        'Europe/Berlin' => 'Berlin / Central Europe',
        'America/New_York' => 'New York (Eastern)',
        'America/Chicago' => 'Chicago (Central)',
        'America/Los_Angeles' => 'Los Angeles (Pacific)',
        'UTC' => 'UTC',
    ];

    public function index(Request $request)
    {
        $status = in_array($request->status, ['upcoming', 'past', 'all'], true) ? $request->status : 'upcoming';
        $view = $request->view === 'calendar' ? 'calendar' : 'list';

        $base = VirtualClassroom::with(['course', 'tenant'])
            ->withCount([
                'attendees',
                'attendees as attended_count' => fn ($q) => $q->where('status', 'attended'),
            ])
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"));

        $data = ['status' => $status, 'view' => $view, 'platforms' => self::PLATFORMS, 'showTenant' => $this->isPlatformAdmin()];

        if ($view === 'calendar') {
            $month = $this->parseMonth($request->month);
            $sessions = (clone $base)
                ->whereBetween('scheduled_at', [$month->startOfMonth()->startOfWeek(Carbon::SUNDAY), $month->endOfMonth()->endOfWeek(Carbon::SATURDAY)])
                ->orderBy('scheduled_at')
                ->get();

            $byDay = $sessions->groupBy(fn ($s) => $s->scheduled_at->copy()->timezone($s->timezone)->toDateString());

            $data += [
                'month' => $month,
                'prevMonth' => $month->subMonth()->format('Y-m'),
                'nextMonth' => $month->addMonth()->format('Y-m'),
                'byDay' => $byDay,
                'gridStart' => $month->startOfMonth()->startOfWeek(Carbon::SUNDAY),
                'gridEnd' => $month->endOfMonth()->endOfWeek(Carbon::SATURDAY),
                'sessions' => collect(),
            ];

            return view('client.virtual-classrooms.index', $data);
        }

        $query = match ($status) {
            'upcoming' => (clone $base)->where('scheduled_at', '>', now())->orderBy('scheduled_at'),
            'past' => (clone $base)->where('scheduled_at', '<=', now())->orderByDesc('scheduled_at'),
            default => (clone $base)->orderByDesc('scheduled_at'),
        };

        $data['sessions'] = $query->paginate(15)->withQueryString();

        return view('client.virtual-classrooms.index', $data);
    }

    public function create()
    {
        return view('client.virtual-classrooms.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $tenantId = $this->resolveTenantId($request->integer('tenant_id') ?: null);
        $this->assertCourseAllowed($data['course_id'] ?? null, $tenantId);

        VirtualClassroom::withoutTenantScope()->create($data + [
            'tenant_id' => $tenantId,
            'created_by' => Auth::id(),
            'status' => 'scheduled',
        ]);

        return redirect()->route('manage.virtual-classrooms.index')
            ->with('success', 'Virtual classroom session scheduled.');
    }

    public function edit($id)
    {
        $session = VirtualClassroom::findOrFail($id);

        return view('client.virtual-classrooms.edit', $this->formData($session) + [
            'session' => $session,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, $id)
    {
        $session = VirtualClassroom::findOrFail($id);
        $data = $this->validated($request);
        $this->assertCourseAllowed($data['course_id'] ?? null, $session->tenant_id);

        $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'recording_url' => ['nullable', 'url', 'max:2000'],
        ]);

        $session->update($data + [
            'status' => $request->input('status'),
            'recording_url' => $request->input('recording_url') ?: null,
        ]);

        return redirect()->route('manage.virtual-classrooms.index')
            ->with('success', 'Session updated successfully.');
    }

    public function destroy($id)
    {
        VirtualClassroom::findOrFail($id)->delete();

        return redirect()->route('manage.virtual-classrooms.index')
            ->with('success', 'Session deleted.');
    }

    public function attendance($id)
    {
        $session = VirtualClassroom::with(['attendees.user.department', 'course'])->findOrFail($id);

        $registeredIds = $session->attendees->pluck('user_id');

        $candidates = User::withoutTenantScope()
            ->where('tenant_id', $session->tenant_id)
            ->where('is_active', true)
            ->whereNotIn('id', $registeredIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $attendees = $session->attendees->sortBy(fn ($a) => strtolower($a->user?->name ?? ''))->values();

        return view('client.virtual-classrooms.attendance', compact('session', 'attendees', 'candidates'));
    }

    public function registerUsers(Request $request, $id)
    {
        $session = VirtualClassroom::findOrFail($id);

        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer'],
        ], ['user_ids.required' => 'Select at least one user to register.']);

        $userIds = User::withoutTenantScope()
            ->where('tenant_id', $session->tenant_id)
            ->where('is_active', true)
            ->whereIn('id', $validated['user_ids'])
            ->pluck('id');

        $registered = 0;
        $skipped = 0;

        DB::transaction(function () use ($session, $userIds, &$registered, &$skipped) {
            // Lock the session row so concurrent registrations respect capacity
            VirtualClassroom::withoutTenantScope()->whereKey($session->id)->lockForUpdate()->first();

            foreach ($userIds as $userId) {
                $full = $session->max_participants
                    && $session->attendees()->count() >= $session->max_participants;

                if ($full) {
                    $skipped++;
                    continue;
                }

                $attendee = VirtualClassroomAttendee::firstOrCreate(
                    ['virtual_classroom_id' => $session->id, 'user_id' => $userId],
                    ['status' => 'registered']
                );

                $attendee->wasRecentlyCreated ? $registered++ : $skipped++;
            }
        });

        $message = "{$registered} user(s) registered.";
        if ($skipped) {
            $message .= " {$skipped} skipped (already registered or session full).";
        }

        return back()->with('success', $message);
    }

    public function markAttendance(Request $request, $id)
    {
        $session = VirtualClassroom::findOrFail($id);

        $validated = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*' => ['required', Rule::in(['attended', 'absent', 'registered'])],
        ]);

        DB::transaction(function () use ($session, $validated) {
            $attendees = $session->attendees()->whereIn('user_id', array_keys($validated['attendance']))->get();

            foreach ($attendees as $attendee) {
                $status = $validated['attendance'][$attendee->user_id];

                $attendee->status = $status;
                if ($status === 'attended') {
                    $attendee->joined_at ??= $session->scheduled_at;
                    $attendee->duration_minutes ??= $session->duration_minutes;
                } else {
                    $attendee->joined_at = null;
                    $attendee->left_at = null;
                    $attendee->duration_minutes = null;
                }
                $attendee->save();
            }

            if ($session->status !== 'cancelled' && $session->isPast()) {
                $session->update(['status' => 'completed']);
            }
        });

        return back()->with('success', 'Attendance saved.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'platform' => ['required', Rule::in(array_keys(self::PLATFORMS))],
            'meeting_url' => ['required', 'url', 'max:2000'],
            'meeting_id' => ['nullable', 'string', 'max:100'],
            'passcode' => ['nullable', 'string', 'max:100'],
            'host_name' => ['nullable', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
            'timezone' => ['required', Rule::in(array_keys(self::TIMEZONES))],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'recurrence_pattern' => ['nullable', Rule::in(self::RECURRENCE), 'required_if:is_recurring,1'],
            'tenant_id' => [$this->isPlatformAdmin() ? 'required' : 'nullable', 'integer', 'exists:tenants,id'],
        ]);

        // The picker value is wall-clock time in the chosen timezone; store as app-timezone (UTC).
        $data['scheduled_at'] = Carbon::parse($data['scheduled_at'], $data['timezone'])
            ->setTimezone(config('app.timezone'));

        $data['is_recurring'] = $request->boolean('is_recurring');
        $data['recurrence_pattern'] = $data['is_recurring'] ? $data['recurrence_pattern'] : null;

        unset($data['tenant_id']);

        return $data;
    }

    private function assertCourseAllowed(?int $courseId, ?int $tenantId): void
    {
        if (!$courseId) {
            return;
        }

        $ok = Course::where('id', $courseId)
            ->where(fn ($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId))
            ->exists();

        if (!$ok) {
            throw ValidationException::withMessages(['course_id' => 'The selected course is not available for this organization.']);
        }
    }

    private function parseMonth(?string $value): CarbonImmutable
    {
        try {
            return $value && preg_match('/^\d{4}-\d{2}$/', $value)
                ? CarbonImmutable::createFromFormat('!Y-m', $value)
                : CarbonImmutable::now()->startOfMonth();
        } catch (\Throwable) {
            return CarbonImmutable::now()->startOfMonth();
        }
    }

    private function formData(?VirtualClassroom $session = null): array
    {
        $tenantId = $session?->tenant_id ?? Auth::user()->tenant_id;

        $courses = Course::when($tenantId, fn ($q) => $q->where(fn ($w) => $w->whereNull('tenant_id')->orWhere('tenant_id', $tenantId)))
            ->orderBy('title')
            ->get(['id', 'title']);

        return [
            'tenants' => $session ? null : $this->tenantOptions(),
            'courses' => $courses,
            'platforms' => self::PLATFORMS,
            'timezones' => self::TIMEZONES,
            'recurrence' => self::RECURRENCE,
        ];
    }
}
