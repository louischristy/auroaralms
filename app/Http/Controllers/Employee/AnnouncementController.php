<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $announcements = $this->visibleTo($user)
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(12);

        $readIds = AnnouncementRead::where('user_id', $user->id)
            ->whereIn('announcement_id', $announcements->pluck('id'))
            ->pluck('announcement_id')
            ->all();

        $unreadCount = $this->visibleTo($user)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        return view('employee.announcements.index', compact('announcements', 'readIds', 'unreadCount'));
    }

    public function show($id)
    {
        $announcement = $this->visibleTo(Auth::user())->with('creator')->findOrFail($id);
        $this->record($announcement);

        return view('employee.announcements.show', compact('announcement'));
    }

    public function markRead(Request $request, $id)
    {
        $announcement = $this->visibleTo(Auth::user())->findOrFail($id);
        $this->record($announcement);

        if ($request->expectsJson()) {
            return response()->json(['read' => true]);
        }

        return back();
    }

    private function record(Announcement $announcement): void
    {
        AnnouncementRead::firstOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => Auth::id()],
            ['read_at' => now()]
        );
    }

    /** Published, unexpired announcements that match the user's role and department. */
    private function visibleTo($user)
    {
        $audiences = ['all', 'employees'];
        if ($user->hasAnyRole(['manager', 'client-admin', 'platform-admin'])) {
            $audiences[] = 'managers';
        }
        if ($user->hasAnyRole(['client-admin', 'platform-admin'])) {
            $audiences[] = 'admins';
        }

        return Announcement::active()
            ->whereIn('target_audience', $audiences)
            ->where(function ($q) use ($user) {
                $q->whereNull('target_department_id');
                if ($user->department_id) {
                    $q->orWhere('target_department_id', $user->department_id);
                }
            });
    }
}
