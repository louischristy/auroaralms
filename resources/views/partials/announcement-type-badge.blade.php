@php
    $badgeClasses = [
        'info' => 'bg-blue-100 text-blue-800',
        'warning' => 'bg-yellow-100 text-yellow-800',
        'urgent' => 'bg-red-100 text-red-800',
        'success' => 'bg-green-100 text-green-800',
    ][$type] ?? 'bg-gray-100 text-gray-800';
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses }}">{{ ucfirst($type) }}</span>
