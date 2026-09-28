@php
    $editing = $subscription->exists;
    $v = fn ($k, $d = null) => old($k, $subscription->{$k} ?? $d);
    $dt = fn ($k) => old($k, optional($subscription->{$k})->format('Y-m-d'));
    $planData = $plans->mapWithKeys(fn ($p) => [$p->id => [
        'base' => (float) $p->base_price, 'perUser' => (float) $p->price_per_user, 'currency' => $p->currency,
        'cycle' => $p->billing_cycle, 'minUsers' => (int) $p->min_users, 'maxUsers' => $p->max_users,
        'maxCourses' => $p->max_courses, 'maxStorage' => $p->max_storage_gb,
    ]]);
    $tenantData = $tenants->mapWithKeys(fn ($t) => [$t->id => (int) $t->users_count]);
    $hasOverrides = collect(['custom_price','custom_price_per_user','billing_cycle','currency','custom_max_users','custom_max_courses','custom_max_storage_gb'])
        ->contains(fn ($k) => $v($k) !== null && $v($k) !== '') || (float) $v('discount_percent', 0) > 0;
    $err = fn ($k) => $errors->has($k) ? '<p class="mt-1 text-sm text-red-600">'.e($errors->first($k)).'</p>' : '';
@endphp
@csrf
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6"
     x-data="{
        plans: @js($planData),
        tenants: @js($tenantData),
        tenantId: @js((string) $v('tenant_id', '')),
        planId: @js((string) $v('plan_id', '')),
        open: @js($hasOverrides),
        customPrice: @js((string) $v('custom_price', '')),
        customPerUser: @js((string) $v('custom_price_per_user', '')),
        discount: @js((string) $v('discount_percent', 0)),
        cycle: @js((string) $v('billing_cycle', '')),
        currency: @js((string) $v('currency', '')),
        customMaxUsers: @js((string) $v('custom_max_users', '')),
        get plan() { return this.plans[this.planId] || null },
        get users() { return this.tenants[this.tenantId] ?? 0 },
        get cur() { return this.currency || (this.plan ? this.plan.currency : 'USD') },
        get sym() { return ({USD: '$', MYR: 'RM ', SGD: 'S$', AED: 'AED ', SAR: 'SAR '})[this.cur] || (this.cur + ' ') },
        get base() { return this.customPrice !== '' ? parseFloat(this.customPrice) || 0 : (this.plan ? this.plan.base : 0) },
        get perUser() { return this.customPerUser !== '' ? parseFloat(this.customPerUser) || 0 : (this.plan ? this.plan.perUser : 0) },
        get maxUsers() { return this.customMaxUsers !== '' ? parseInt(this.customMaxUsers) : (this.plan ? this.plan.maxUsers : null) },
        get billable() {
            let n = this.users;
            if (this.maxUsers) n = Math.min(n, this.maxUsers);
            return Math.max(n, this.plan ? this.plan.minUsers : 0);
        },
        get disc() { return Math.min(Math.max(parseFloat(this.discount) || 0, 0), 100) },
        get subtotal() { return this.base + this.billable * this.perUser },
        get monthly() { return this.subtotal * (1 - this.disc / 100) },
        get months() { return ({monthly: 1, quarterly: 3, yearly: 12})[this.cycle || (this.plan ? this.plan.cycle : 'monthly')] || 1 },
        money(n) { return this.sym + n.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) },
        ph(v) { return v === null || v === undefined ? 'Plan default: unlimited' : 'Plan default: ' + v }
     }">
    <div class="lg:col-span-2 space-y-6">
        {{-- Tenant & Plan --}}
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold text-gray-900">Tenant &amp; Plan</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="label" for="tenant_id">Tenant *</label>
                    @if($editing)
                        <input class="input bg-gray-50" value="{{ $subscription->tenant->name }}" disabled>
                        <input type="hidden" x-model="tenantId">
                    @else
                        <select class="input" id="tenant_id" name="tenant_id" x-model="tenantId" required>
                            <option value="">Select tenant…</option>
                            @foreach($tenants as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->users_count }} users)</option>
                            @endforeach
                        </select>
                        @if($tenants->isEmpty())<p class="mt-1 text-xs text-amber-600">All tenants already have an active subscription.</p>@endif
                    @endif
                    {!! $err('tenant_id') !!}
                </div>
                <div>
                    <label class="label" for="plan_id">Plan *</label>
                    <select class="input" id="plan_id" name="plan_id" x-model="planId" required>
                        <option value="">Select plan…</option>
                        @foreach($plans as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}{{ $p->is_active ? '' : ' (inactive)' }}</option>
                        @endforeach
                    </select>
                    {!! $err('plan_id') !!}
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="label" for="status">Status</label>
                    <select class="input" id="status" name="status">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" @selected($v('status', 'active') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    {!! $err('status') !!}
                </div>
                <div>
                    <label class="label" for="starts_at">Starts *</label>
                    <input type="date" class="input" id="starts_at" name="starts_at" value="{{ $dt('starts_at') }}" required>
                    {!! $err('starts_at') !!}
                </div>
                <div>
                    <label class="label" for="expires_at">Expires</label>
                    <input type="date" class="input" id="expires_at" name="expires_at" value="{{ $dt('expires_at') }}">
                    {!! $err('expires_at') !!}
                </div>
            </div>
        </div>

        {{-- Overrides --}}
        <div class="card">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-6 text-left">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Customize Pricing</h2>
                    <p class="text-sm text-gray-500">Override plan defaults for this tenant. Leave blank to use the plan value.</p>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="px-6 pb-6 space-y-4 border-t border-gray-100 pt-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="label" for="custom_price">Custom Base Price</label>
                        <input type="number" step="0.01" min="0" class="input" id="custom_price" name="custom_price" x-model="customPrice" :placeholder="plan ? 'Plan default: ' + plan.base : 'Select a plan'">
                        {!! $err('custom_price') !!}
                    </div>
                    <div>
                        <label class="label" for="custom_price_per_user">Custom Price per User</label>
                        <input type="number" step="0.01" min="0" class="input" id="custom_price_per_user" name="custom_price_per_user" x-model="customPerUser" :placeholder="plan ? 'Plan default: ' + plan.perUser : 'Select a plan'">
                        {!! $err('custom_price_per_user') !!}
                    </div>
                    <div>
                        <label class="label" for="discount_percent">Discount %</label>
                        <input type="number" step="0.01" min="0" max="100" class="input" id="discount_percent" name="discount_percent" x-model="discount">
                        {!! $err('discount_percent') !!}
                    </div>
                    <div>
                        <label class="label" for="discount_reason">Discount Reason</label>
                        <input class="input" id="discount_reason" name="discount_reason" value="{{ $v('discount_reason') }}" placeholder="e.g. Annual prepay, NGO rate">
                        {!! $err('discount_reason') !!}
                    </div>
                    <div>
                        <label class="label" for="billing_cycle">Billing Cycle</label>
                        <select class="input" id="billing_cycle" name="billing_cycle" x-model="cycle">
                            <option value="">Plan default</option>
                            @foreach($cycles as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="currency">Currency</label>
                        <select class="input" id="currency" name="currency" x-model="currency">
                            <option value="">Plan default</option>
                            @foreach($currencies as $c)<option value="{{ $c }}">{{ $c }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="label" for="custom_max_users">Max Users</label>
                        <input type="number" min="1" class="input" id="custom_max_users" name="custom_max_users" x-model="customMaxUsers" :placeholder="plan ? ph(plan.maxUsers) : 'Select a plan'">
                        {!! $err('custom_max_users') !!}
                    </div>
                    <div>
                        <label class="label" for="custom_max_courses">Max Courses</label>
                        <input type="number" min="1" class="input" id="custom_max_courses" name="custom_max_courses" value="{{ $v('custom_max_courses') }}" :placeholder="plan ? ph(plan.maxCourses) : 'Select a plan'">
                        {!! $err('custom_max_courses') !!}
                    </div>
                    <div>
                        <label class="label" for="custom_max_storage_gb">Max Storage (GB)</label>
                        <input type="number" min="1" class="input" id="custom_max_storage_gb" name="custom_max_storage_gb" value="{{ $v('custom_max_storage_gb') }}" :placeholder="plan ? ph(plan.maxStorage) : 'Select a plan'">
                        {!! $err('custom_max_storage_gb') !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <label class="label" for="notes">Notes</label>
            <textarea class="input" id="notes" name="notes" rows="3" placeholder="Internal notes, contract references…">{{ $v('notes') }}</textarea>
            {!! $err('notes') !!}
        </div>
    </div>

    {{-- Live calculator --}}
    <div>
        <div class="card p-6 space-y-4 lg:sticky lg:top-6">
            <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Price Calculator
            </h2>
            <template x-if="!plan">
                <p class="text-sm text-gray-400">Select a plan to see the estimated cost.</p>
            </template>
            <template x-if="plan">
                <div class="space-y-4">
                    <div class="rounded-lg bg-gray-50 p-3 text-sm text-gray-700 leading-relaxed">
                        <span class="font-medium">Base:</span> <span x-text="money(base)"></span>
                        + (<span x-text="billable"></span> users &times; <span x-text="money(perUser)"></span>/user)
                        = <span class="font-medium" x-text="money(subtotal)"></span>
                        <template x-if="disc > 0">
                            <span>&minus; <span x-text="disc"></span>% = <span class="font-semibold" x-text="money(monthly)"></span></span>
                        </template>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-gray-500">Users (current / billable)</dt><dd class="font-medium"><span x-text="users"></span> / <span x-text="billable"></span></dd></div>
                        <div class="flex justify-between"><dt class="text-gray-500">Discount</dt><dd class="font-medium text-green-600" x-text="'−' + money(subtotal - monthly)"></dd></div>
                    </dl>
                    <div class="rounded-lg bg-primary/10 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-500">Monthly</p>
                        <p class="text-2xl font-bold text-primary" x-text="money(monthly)"></p>
                        <p class="mt-2 text-xs uppercase tracking-wide text-gray-500">Yearly</p>
                        <p class="text-lg font-semibold text-gray-900" x-text="money(monthly * 12)"></p>
                        <p class="mt-2 text-xs text-gray-500">Invoiced <span x-text="months === 1 ? 'monthly' : 'every ' + months + ' months'"></span>: <span class="font-medium" x-text="money(monthly * months)"></span></p>
                    </div>
                    <p class="text-xs text-gray-400">Estimate based on the tenant's current user count.</p>
                </div>
            </template>
            <div class="pt-2 flex gap-2">
                <button type="submit" class="btn-primary flex-1">{{ $submitLabel }}</button>
                <a href="{{ route('platform.subscriptions.index') }}" class="btn-outline">Cancel</a>
            </div>
        </div>
    </div>
</div>
