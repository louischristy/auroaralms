@php
    $v = fn ($k, $d = null) => old($k, $plan->{$k} ?? $d);
    $selectedModules = old('modules', $plan->modules ?? []);
    $err = fn ($k) => $errors->has($k) ? '<p class="mt-1 text-sm text-red-600">'.e($errors->first($k)).'</p>' : '';
@endphp
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Basic --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Basic Information
            </h2>
            <div>
                <label class="label" for="name">Plan Name *</label>
                <input class="input" id="name" name="name" value="{{ $v('name') }}" required>
                {!! $err('name') !!}
            </div>
            <div>
                <label class="label" for="description">Description</label>
                <textarea class="input" id="description" name="description" rows="2">{{ $v('description') }}</textarea>
                {!! $err('description') !!}
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="billing_cycle">Billing Cycle *</label>
                    <select class="input" id="billing_cycle" name="billing_cycle">
                        @foreach($cycles as $key => $label)
                            <option value="{{ $key }}" @selected($v('billing_cycle') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    {!! $err('billing_cycle') !!}
                </div>
                <div>
                    <label class="label" for="trial_days">Trial Days</label>
                    <input type="number" min="0" class="input" id="trial_days" name="trial_days" value="{{ $v('trial_days', 0) }}">
                    {!! $err('trial_days') !!}
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Pricing
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label" for="base_price">Base Price *</label>
                    <input type="number" step="0.01" min="0" class="input" id="base_price" name="base_price" value="{{ $v('base_price', 0) }}" required>
                    {!! $err('base_price') !!}
                </div>
                <div>
                    <label class="label" for="price_per_user">Price per User *</label>
                    <input type="number" step="0.01" min="0" class="input" id="price_per_user" name="price_per_user" value="{{ $v('price_per_user', 0) }}" required>
                    {!! $err('price_per_user') !!}
                </div>
                <div>
                    <label class="label" for="currency">Currency *</label>
                    <select class="input" id="currency" name="currency">
                        @foreach($currencies as $c)
                            <option value="{{ $c }}" @selected($v('currency') === $c)>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="min_users">Min Users *</label>
                    <input type="number" min="1" class="input" id="min_users" name="min_users" value="{{ $v('min_users', 1) }}" required>
                    {!! $err('min_users') !!}
                </div>
                <div>
                    <label class="label" for="max_users">Max Users</label>
                    <input type="number" min="1" class="input" id="max_users" name="max_users" value="{{ $v('max_users') }}" placeholder="Unlimited">
                    {!! $err('max_users') !!}
                </div>
            </div>
            <p class="text-xs text-gray-500">Monthly cost = base price + (billable users x price per user).</p>
        </div>

        {{-- Limits --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                Limits
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="max_courses">Max Courses</label>
                    <input type="number" min="1" class="input" id="max_courses" name="max_courses" value="{{ $v('max_courses') }}" placeholder="Unlimited">
                    {!! $err('max_courses') !!}
                </div>
                <div>
                    <label class="label" for="max_storage_gb">Max Storage (GB)</label>
                    <input type="number" min="1" class="input" id="max_storage_gb" name="max_storage_gb" value="{{ $v('max_storage_gb') }}" placeholder="Unlimited">
                    {!! $err('max_storage_gb') !!}
                </div>
            </div>
        </div>

        {{-- Features --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Features
            </h2>
            <div class="divide-y divide-gray-100">
                @foreach($featureLabels as $key => $label)
                    <div class="flex items-center justify-between py-3" x-data="{ on: @js((bool) $v($key, false)) }">
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                        <input type="hidden" name="{{ $key }}" :value="on ? 1 : 0">
                        <button type="button" role="switch" :aria-checked="on" @click="on = !on"
                                class="relative inline-flex h-6 w-11 shrink-0 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary/40"
                                :class="on ? 'bg-primary' : 'bg-gray-300'">
                            <span class="inline-block h-5 w-5 mt-0.5 rounded-full bg-white shadow transition-transform"
                                  :class="on ? 'translate-x-[22px]' : 'translate-x-0.5'"></span>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Modules --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Available Modules
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($modules as $key => $label)
                    <label class="flex items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="modules[]" value="{{ $key }}" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(in_array($key, $selectedModules))>
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">
        <div class="card p-6 space-y-4 lg:sticky lg:top-6">
            <h2 class="text-base font-semibold text-gray-900">Display</h2>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(old('is_active', $plan->is_active ?? true))>
                <span class="text-sm text-gray-700">Active (available for new subscriptions)</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="is_featured" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" @checked(old('is_featured', $plan->is_featured ?? false))>
                <span class="text-sm text-gray-700">Featured ("Most popular")</span>
            </label>
            <div>
                <label class="label" for="sort_order">Sort Order</label>
                <input type="number" class="input" id="sort_order" name="sort_order" value="{{ $v('sort_order', 0) }}">
            </div>
            <div class="pt-2 flex gap-2">
                <button type="submit" class="btn-primary flex-1">{{ $submitLabel }}</button>
                <a href="{{ route('platform.subscription-plans.index') }}" class="btn-outline">Cancel</a>
            </div>
        </div>
    </div>
</div>
