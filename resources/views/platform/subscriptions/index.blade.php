@extends('layouts.app')
@section('title', 'Subscriptions — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@php
    $sym = ['USD' => '$', 'MYR' => 'RM ', 'SGD' => 'S$', 'AED' => 'AED ', 'SAR' => 'SAR '];
    $tabs = ['all' => 'All', 'active' => 'Active', 'trial' => 'Trial', 'expired' => 'Expired', 'cancelled' => 'Cancelled'];
    $badge = function ($s) {
        $st = $s->isCancelled() ? 'cancelled' : ($s->isExpired() ? 'expired' : $s->status);
        $cls = ['active' => 'badge-success', 'trial' => 'badge-info', 'suspended' => 'badge-warning', 'cancelled' => 'badge-danger', 'expired' => 'badge-danger'][$st] ?? 'badge-info';
        return [$st, $cls];
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscriptions</h1>
            <p class="text-sm text-gray-500 mt-1">Tenant billing, custom pricing and invoices.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('platform.subscription-plans.index') }}" class="btn-outline">Pricing Plans</a>
            <a href="{{ route('platform.subscriptions.create') }}" class="btn-primary">+ New Subscription</a>
        </div>
    </div>

    <div class="card">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200 px-4 pt-3">
            <nav class="flex gap-1 -mb-px overflow-x-auto">
                @foreach($tabs as $key => $label)
                    <a href="{{ route('platform.subscriptions.index', array_filter(['status' => $key === 'all' ? null : $key, 'search' => $search])) }}"
                       class="whitespace-nowrap px-3 py-2 text-sm font-medium border-b-2 {{ $status === $key ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                        {{ $label }} <span class="ml-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-600">{{ $counts[$key] }}</span>
                    </a>
                @endforeach
            </nav>
            <form method="GET" class="pb-2 flex items-center gap-2">
                @if($status !== 'all')<input type="hidden" name="status" value="{{ $status }}">@endif
                <div class="relative">
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search tenant…" class="input pl-9 w-56">
                </div>
                <button class="btn-secondary btn-sm">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tenant</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Plan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Monthly Cost</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Users</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Expires</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $sub)
                        @php
                            [$st, $cls] = $badge($sub);
                            $users = $sub->tenant?->users_count ?? 0;
                            $cur = $sub->currency ?: ($sub->plan?->currency ?? 'USD');
                            $max = $sub->getEffectiveMaxUsers();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $sub->tenant?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $sub->plan?->name ?? '—' }}
                                @if($sub->custom_price !== null || $sub->custom_price_per_user !== null)<span class="badge-info ml-1">Custom</span>@endif
                                @if((float) $sub->discount_percent > 0)<span class="badge-success ml-1">-{{ rtrim(rtrim(number_format($sub->discount_percent, 2), '0'), '.') }}%</span>@endif
                            </td>
                            <td class="px-4 py-3 text-center"><span class="{{ $cls }}">{{ ucfirst($st) }}</span></td>
                            <td class="px-4 py-3 text-right font-medium">{{ $sym[$cur] ?? $cur.' ' }}{{ number_format($sub->calculateMonthlyTotal($users), 2) }}</td>
                            <td class="px-4 py-3 text-center text-gray-600">{{ $users }}@if($max) / {{ $max }}@endif</td>
                            <td class="px-4 py-3 text-gray-600">
                                @if($sub->expires_at)
                                    <span class="{{ $sub->expires_at->isPast() ? 'text-red-600' : ($sub->expires_at->diffInDays(now()) < 30 ? 'text-amber-600' : '') }}">{{ $sub->expires_at->format('d M Y') }}</span>
                                @else <span class="text-gray-400">No expiry</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                    <a href="{{ route('platform.subscriptions.edit', $sub->id) }}" class="text-secondary hover:text-primary">Edit</a>
                                    <form method="POST" action="{{ route('platform.subscriptions.generate-invoice', $sub->id) }}">@csrf
                                        <button class="text-secondary hover:text-primary">Invoice</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">No subscriptions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($subscriptions->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</div>
@endsection
