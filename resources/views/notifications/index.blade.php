@extends('layouts.app')
@section('title', 'Notifications — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
        @if($notifications->where('read_at', null)->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="text-sm text-primary hover:text-primary/80">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="card divide-y divide-gray-100">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <a href="{{ route('notifications.read', $notification->id) }}"
               class="flex items-start gap-4 px-6 py-4 hover:bg-gray-50 transition {{ $notification->read_at ? '' : 'bg-blue-50/50' }}">
                <div class="flex-shrink-0 mt-1">
                    @if(($data['type'] ?? '') === 'training_due_reminder')
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @elseif(($data['type'] ?? '') === 'training_overdue')
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                    @elseif(($data['type'] ?? '') === 'policy_pushed')
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    @else
                        <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm {{ $notification->read_at ? 'text-gray-600' : 'text-gray-900 font-medium' }}">
                        @if(($data['type'] ?? '') === 'training_due_reminder')
                            <strong>{{ $data['course_title'] }}</strong> is due in {{ $data['days_remaining'] }} day(s)
                        @elseif(($data['type'] ?? '') === 'training_overdue')
                            <strong>{{ $data['course_title'] }}</strong> is overdue (was due {{ $data['due_date'] }})
                        @elseif(($data['type'] ?? '') === 'policy_pushed')
                            New policy requires your acknowledgment: <strong>{{ $data['policy_title'] }}</strong>
                        @else
                            You have a new notification
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if(! $notification->read_at)
                    <span class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></span>
                @endif
            </a>
        @empty
            <div class="px-6 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p>No notifications yet.</p>
            </div>
        @endforelse
    </div>

    <div class="flex justify-center">{{ $notifications->links() }}</div>
</div>
@endsection
