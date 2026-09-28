@php
    $endsAt = $session->scheduled_at->copy()->addMinutes($session->duration_minutes ?? 60);
    if ($session->status === 'cancelled') { [$label, $cls] = ['Cancelled', 'bg-red-100 text-red-800']; }
    elseif ($session->status === 'completed') { [$label, $cls] = ['Completed', 'bg-gray-100 text-gray-700']; }
    elseif ($session->status === 'live' || ($session->scheduled_at->isPast() && $endsAt->isFuture())) { [$label, $cls] = ['Live', 'bg-green-100 text-green-800']; }
    elseif ($session->scheduled_at->isPast()) { [$label, $cls] = ['Ended', 'bg-gray-100 text-gray-700']; }
    else { [$label, $cls] = ['Upcoming', 'bg-blue-100 text-blue-800']; }
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}</span>
