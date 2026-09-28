@extends('layouts.app')
@section('title', 'Pricing Plans — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@php
    $sym = ['USD' => '$', 'MYR' => 'RM', 'SGD' => 'S$', 'AED' => 'AED ', 'SAR' => 'SAR '];
    $money = fn ($p, $a) => ($sym[$p->currency] ?? $p->currency.' ') . number_format((float) $a, 2);
    $sortUrl = fn ($col) => route('platform.subscription-plans.index', ['sort' => $col, 'dir' => ($sort === $col && $dir === 'asc') ? 'desc' : 'asc']);
    $th = fn ($col, $label, $align = 'left') => '<th class="text-'.$align.' px-4 py-3 font-medium text-gray-600"><a href="'.$sortUrl($col).'" class="inline-flex items-center gap-1 hover:text-primary">'.$label.($sort === $col ? ($dir === 'asc' ? ' &uarr;' : ' &darr;') : '').'</a></th>';
    $check = '<svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
    $cross = '<svg class="w-4 h-4 text-gray-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
@endphp

@section('content')
<div class="space-y-6" x-data="{ view: 'cards' }">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pricing Plans</h1>
            <p class="text-sm text-gray-500 mt-1">Manage the plans offered to client organizations.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5">
                <button type="button" @click="view = 'cards'" :class="view === 'cards' ? 'bg-primary text-white' : 'text-gray-600'" class="px-3 py-1.5 text-xs font-medium rounded-md">Cards</button>
                <button type="button" @click="view = 'table'" :class="view === 'table' ? 'bg-primary text-white' : 'text-gray-600'" class="px-3 py-1.5 text-xs font-medium rounded-md">Table</button>
            </div>
            <a href="{{ route('platform.subscriptions.index') }}" class="btn-outline">Subscriptions</a>
            <a href="{{ route('platform.subscription-plans.create') }}" class="btn-primary">+ New Plan</a>
        </div>
    </div>

    {{-- Cards --}}
    <div x-show="view === 'cards'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($plans as $plan)
            <div class="card relative flex flex-col p-6 {{ $plan->is_featured ? 'ring-2 ring-primary' : '' }} {{ $plan->is_active ? '' : 'opacity-70' }}">
                @if($plan->is_featured)
                    <span class="absolute -top-3 left-6 rounded-full bg-primary px-3 py-0.5 text-xs font-semibold text-white">Most popular</span>
                @endif
                <div class="flex items-start justify-between gap-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                    @unless($plan->is_active)<span class="badge-warning">Inactive</span>@endunless
                </div>
                @if($plan->description)<p class="mt-1 text-sm text-gray-500">{{ $plan->description }}</p>@endif

                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ $money($plan, $plan->base_price) }}</span>
                    <span class="text-sm text-gray-500">/ {{ ['monthly' => 'month', 'quarterly' => 'quarter', 'yearly' => 'year'][$plan->billing_cycle] ?? 'custom' }}</span>
                    <p class="text-sm text-gray-600 mt-1">+ {{ $money($plan, $plan->price_per_user) }} per user</p>
                </div>

                <ul class="mt-5 space-y-2 text-sm text-gray-700 flex-1">
                    <li class="flex gap-2">{!! $check !!} {{ $plan->min_users }}{{ $plan->max_users ? '–'.$plan->max_users : '+' }} users</li>
                    <li class="flex gap-2">{!! $check !!} {{ $plan->max_courses ? $plan->max_courses.' courses' : 'Unlimited courses' }}</li>
                    <li class="flex gap-2">{!! $check !!} {{ $plan->max_storage_gb ? $plan->max_storage_gb.' GB storage' : 'Unlimited storage' }}</li>
                    @if($plan->trial_days)<li class="flex gap-2">{!! $check !!} {{ $plan->trial_days }}-day free trial</li>@endif
                    @foreach($featureLabels as $key => $label)
                        <li class="flex gap-2 {{ $plan->{$key} ? '' : 'text-gray-400' }}">{!! $plan->{$key} ? $check : $cross !!} {{ $label }}</li>
                    @endforeach
                </ul>

                @if(!empty($plan->modules))
                    <div class="mt-4 flex flex-wrap gap-1.5">
                        @foreach($plan->modules as $m)
                            <span class="badge-info">{{ $modules[$m] ?? $m }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="mt-5 pt-4 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
                    <span>{{ $plan->active_subscriptions_count }} active subscription{{ $plan->active_subscriptions_count === 1 ? '' : 's' }}</span>
                    <div class="flex items-center gap-3 text-sm">
                        <a href="{{ route('platform.subscription-plans.edit', $plan->id) }}" class="font-medium text-secondary hover:text-primary">Edit</a>
                        <form method="POST" action="{{ route('platform.subscription-plans.duplicate', $plan->id) }}">@csrf
                            <button class="font-medium text-secondary hover:text-primary">Duplicate</button></form>
                        <form method="POST" action="{{ route('platform.subscription-plans.destroy', $plan->id) }}" @submit="if (!confirm('Delete this plan?')) $event.preventDefault()">@csrf @method('DELETE')
                            <button class="font-medium text-red-600 hover:text-red-800">Delete</button></form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-10 text-center text-gray-400 md:col-span-2 xl:col-span-3">No plans yet. Create your first plan.</div>
        @endforelse
    </div>

    {{-- Table --}}
    <div x-show="view === 'table'" x-cloak class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        {!! $th('name', 'Plan') !!}
                        {!! $th('billing_cycle', 'Cycle') !!}
                        {!! $th('base_price', 'Base Price', 'right') !!}
                        {!! $th('price_per_user', 'Per User', 'right') !!}
                        {!! $th('max_users', 'Users', 'center') !!}
                        {!! $th('subscriptions_count', 'Subscriptions', 'center') !!}
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($plans as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }} @if($plan->is_featured)<span class="badge-info ml-1">Featured</span>@endif</td>
                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $plan->billing_cycle }}</td>
                            <td class="px-4 py-3 text-right">{{ $money($plan, $plan->base_price) }}</td>
                            <td class="px-4 py-3 text-right">{{ $money($plan, $plan->price_per_user) }}</td>
                            <td class="px-4 py-3 text-center">{{ $plan->min_users }}–{{ $plan->max_users ?? '∞' }}</td>
                            <td class="px-4 py-3 text-center">{{ $plan->subscriptions_count }}</td>
                            <td class="px-4 py-3 text-center">{!! $plan->is_active ? '<span class="badge-success">Active</span>' : '<span class="badge-warning">Inactive</span>' !!}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-3 text-xs font-medium">
                                    <a href="{{ route('platform.subscription-plans.edit', $plan->id) }}" class="text-secondary hover:text-primary">Edit</a>
                                    <form method="POST" action="{{ route('platform.subscription-plans.duplicate', $plan->id) }}">@csrf<button class="text-secondary hover:text-primary">Duplicate</button></form>
                                    <form method="POST" action="{{ route('platform.subscription-plans.destroy', $plan->id) }}" @submit="if (!confirm('Delete this plan?')) $event.preventDefault()">@csrf @method('DELETE')<button class="text-red-600 hover:text-red-800">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
