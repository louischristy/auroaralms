@php
    $size = $size ?? 'w-10 h-10';
    $styles = [
        'zoom' => ['bg-blue-100 text-blue-600', 'Zoom'],
        'teams' => ['bg-indigo-100 text-indigo-600', 'Microsoft Teams'],
        'meet' => ['bg-green-100 text-green-600', 'Google Meet'],
        'webex' => ['bg-teal-100 text-teal-600', 'Webex'],
        'other' => ['bg-gray-100 text-gray-600', 'Other'],
    ];
    [$iconClasses, $iconLabel] = $styles[$platform] ?? $styles['other'];
@endphp
<span class="inline-flex items-center justify-center rounded-lg {{ $size }} {{ $iconClasses }} flex-shrink-0" title="{{ $iconLabel }}">
    @if($platform === 'teams')
        {{-- Users / group --}}
        <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    @elseif($platform === 'meet')
        {{-- Video camera (alt) --}}
        <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
    @elseif($platform === 'webex')
        {{-- Globe --}}
        <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    @elseif($platform === 'other')
        {{-- Link --}}
        <svg class="w-1/2 h-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
    @else
        {{-- Zoom: video camera --}}
        <svg class="w-1/2 h-1/2" fill="currentColor" viewBox="0 0 24 24"><path d="M4 6a2 2 0 00-2 2v8a2 2 0 002 2h9a2 2 0 002-2v-2.4l4.6 3.2A1 1 0 0021 15.9V8.1a1 1 0 00-1.4-.9L15 10.4V8a2 2 0 00-2-2H4z"/></svg>
    @endif
</span>
