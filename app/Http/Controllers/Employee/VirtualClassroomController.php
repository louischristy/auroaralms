<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\VirtualClassroom;
use App\Models\VirtualClassroomAttendee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VirtualClassroomController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $notCancelled = fn ($q) => $q->where('status', '!=', 'cancelled');

        $registeredIds = VirtualClassroomAttendee::where('user_id', $userId)->pluck('virtual_classroom_id');

        // Sessions in progress count as upcoming until their scheduled end
        $registered = VirtualClassroom::with('course')
            ->whereIn('id', $registeredIds)
            ->where($notCancelled)
            ->where('scheduled_at', '>', now()->subHours(6))
            ->orderBy('scheduled_at')
            ->get()
            ->filter(fn ($s) => $s->scheduled_at->copy()->addMinutes($s->duration_minutes)->isFuture())
            ->values();

        $available = VirtualClassroom::with('course')
            ->withCount('attendees')
            ->whereNotIn('id', $registeredIds)
            ->where($notCancelled)
            ->where('scheduled_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('course_id')
                    ->orWhereIn('course_id', function ($sub) {
                        $sub->select('course_id')->from('course_enrollments')->where('user_id', Auth::id());
                    });
            })
            ->orderBy('scheduled_at')
            ->get();

        $past = VirtualClassroom::with('course')
            ->whereIn('id', $registeredIds)
            ->where('scheduled_at', '<=', now())
            ->orderByDesc('scheduled_at')
            ->limit(20)
            ->get()
            ->reject(fn ($s) => $registered->contains('id', $s->id));

        $attendance = VirtualClassroomAttendee::where('user_id', $userId)
            ->pluck('status', 'virtual_classroom_id');

        return view('employee.virtual-classrooms.index', compact('registered', 'available', 'past', 'attendance'));
    }

    public function register($id)
    {
        $session = VirtualClassroom::findOrFail($id);

        if ($session->status === 'cancelled' || !$session->isUpcoming()) {
            return back()->with('error', 'Registration is closed for this session.');
        }

        $result = DB::transaction(function () use ($session) {
            VirtualClassroom::withoutTenantScope()->whereKey($session->id)->lockForUpdate()->first();

            if (VirtualClassroomAttendee::where('virtual_classroom_id', $session->id)->where('user_id', Auth::id())->exists()) {
                return 'already';
            }

            if ($session->max_participants && $session->attendees()->count() >= $session->max_participants) {
                return 'full';
            }

            VirtualClassroomAttendee::create([
                'virtual_classroom_id' => $session->id,
                'user_id' => Auth::id(),
                'status' => 'registered',
            ]);

            return 'ok';
        });

        return match ($result) {
            'already' => back()->with('info', 'You are already registered for this session.'),
            'full' => back()->with('error', 'Sorry, this session is full.'),
            default => back()->with('success', 'You are registered for "' . $session->title . '".'),
        };
    }
}
