<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // Redirect to the relevant page based on notification type
        $data = $notification->data;
        $url = match ($data['type'] ?? '') {
            'training_due_reminder', 'training_overdue' => url("/learn/courses/{$data['course_id']}"),
            'policy_pushed' => url("/learn/policies/{$data['policy_id']}"),
            default => route('notifications.index'),
        };

        return redirect($url);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}
