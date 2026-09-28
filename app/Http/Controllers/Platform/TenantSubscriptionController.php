<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TenantSubscriptionController extends Controller
{
    public const STATUSES = ['active', 'trial', 'suspended', 'cancelled', 'expired'];
    private const CYCLE_MONTHS = ['monthly' => 1, 'quarterly' => 3, 'yearly' => 12, 'custom' => 1];

    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        $query = TenantSubscription::with([
            'plan' => fn ($q) => $q->withTrashed(),
            'tenant' => fn ($q) => $q->withCount('users'),
        ])->latest();

        match ($status) {
            'active' => $query->where('status', 'active')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now())),
            'trial' => $query->where('status', 'trial'),
            'expired' => $query->expired(),
            'cancelled' => $query->where('status', 'cancelled'),
            default => null,
        };

        if ($search !== '') {
            $query->whereHas('tenant', fn ($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $counts = [
            'all' => TenantSubscription::count(),
            'active' => TenantSubscription::where('status', 'active')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))->count(),
            'trial' => TenantSubscription::trial()->count(),
            'expired' => TenantSubscription::expired()->count(),
            'cancelled' => TenantSubscription::where('status', 'cancelled')->count(),
        ];

        $subscriptions = $query->paginate(15)->withQueryString();

        return view('platform.subscriptions.index', compact('subscriptions', 'status', 'search', 'counts'));
    }

    public function create(Request $request)
    {
        $subscription = new TenantSubscription([
            'tenant_id' => $request->query('tenant_id'),
            'plan_id' => $request->query('plan_id'),
            'status' => 'active',
            'discount_percent' => 0,
            'starts_at' => now(),
        ]);

        $tenants = Tenant::withCount('users')
            ->whereDoesntHave('subscriptions', fn ($q) => $q->whereIn('status', ['active', 'trial']))
            ->orderBy('name')->get();

        return view('platform.subscriptions.create', $this->formData($subscription, $tenants));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);

        $exists = TenantSubscription::where('tenant_id', $data['tenant_id'])->whereIn('status', ['active', 'trial'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors(['tenant_id' => 'This tenant already has an active or trial subscription.']);
        }

        $plan = SubscriptionPlan::findOrFail($data['plan_id']);
        if ($data['status'] === 'trial' && $plan->trial_days > 0) {
            $data['trial_ends_at'] = \Carbon\Carbon::parse($data['starts_at'])->addDays($plan->trial_days);
        }

        $subscription = TenantSubscription::create($data);
        $this->syncTenant($subscription);

        return redirect()->route('platform.subscriptions.index')
            ->with('success', 'Subscription created for ' . $subscription->tenant->name . '.');
    }

    public function edit($id)
    {
        $subscription = TenantSubscription::with('invoices')->findOrFail($id);
        $tenants = Tenant::withCount('users')->where('id', $subscription->tenant_id)->get();

        return view('platform.subscriptions.edit', $this->formData($subscription, $tenants));
    }

    public function update(Request $request, $id)
    {
        $subscription = TenantSubscription::findOrFail($id);
        $data = $this->validated($request, false);

        if ($data['status'] === 'cancelled' && $subscription->cancelled_at === null) {
            $data['cancelled_at'] = now();
        } elseif ($data['status'] !== 'cancelled') {
            $data['cancelled_at'] = null;
        }

        $subscription->update($data);
        $this->syncTenant($subscription->fresh());

        return redirect()->route('platform.subscriptions.index')
            ->with('success', 'Subscription updated.');
    }

    public function generateInvoice($id)
    {
        $subscription = TenantSubscription::with(['plan' => fn ($q) => $q->withTrashed(), 'tenant'])->findOrFail($id);

        $users = $subscription->tenant->users()->count();
        $plan = $subscription->plan;
        $cycle = $subscription->billing_cycle ?: $plan->billing_cycle;
        $months = self::CYCLE_MONTHS[$cycle] ?? 1;
        $currency = $subscription->currency ?: $plan->currency;

        $base = $subscription->custom_price !== null ? (float) $subscription->custom_price : (float) $plan->base_price;
        $perUser = $subscription->custom_price_per_user !== null ? (float) $subscription->custom_price_per_user : (float) $plan->price_per_user;
        $maxUsers = $subscription->getEffectiveMaxUsers();
        $billable = $maxUsers ? min($users, $maxUsers) : $users;
        $billable = max($billable, (int) $plan->min_users);

        $subtotal = round(($base + $perUser * $billable) * $months, 2);
        $discountPct = min((float) $subscription->discount_percent, 100);
        $discount = round($subtotal * $discountPct / 100, 2);

        $lines = [[
            'description' => "{$plan->name} plan - platform fee ({$months} " . ($months === 1 ? 'month' : 'months') . ')',
            'quantity' => 1,
            'unit_price' => round($base * $months, 2),
            'amount' => round($base * $months, 2),
        ]];
        if ($perUser > 0) {
            $lines[] = [
                'description' => "Per-user licences ({$billable} users x {$months} " . ($months === 1 ? 'month' : 'months') . ')',
                'quantity' => $billable,
                'unit_price' => round($perUser * $months, 2),
                'amount' => round($perUser * $months * $billable, 2),
            ];
        }

        $invoice = DB::transaction(function () use ($subscription, $subtotal, $discount, $currency, $lines, $discountPct) {
            return SubscriptionInvoice::create([
                'tenant_id' => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'invoice_number' => SubscriptionInvoice::generateInvoiceNumber(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => 0,
                'total' => $subtotal - $discount,
                'currency' => $currency,
                'status' => 'pending',
                'issued_at' => now(),
                'due_at' => now()->addDays(30),
                'line_items' => $lines,
                'notes' => $discountPct > 0
                    ? "Discount {$discountPct}%" . ($subscription->discount_reason ? ": {$subscription->discount_reason}" : '')
                    : null,
            ]);
        });

        return redirect()->route('platform.subscriptions.invoice', $invoice->id)
            ->with('success', "Invoice {$invoice->invoice_number} generated.");
    }

    public function showInvoice($invoiceId)
    {
        $invoice = SubscriptionInvoice::with(['tenant', 'subscription.plan' => fn ($q) => $q->withTrashed()])->findOrFail($invoiceId);

        return view('platform.subscriptions.invoice', compact('invoice'));
    }

    protected function formData(TenantSubscription $subscription, $tenants): array
    {
        $plans = SubscriptionPlan::ordered()->get();
        if ($subscription->plan_id && ! $plans->contains('id', $subscription->plan_id)) {
            $plans->push(SubscriptionPlan::withTrashed()->find($subscription->plan_id));
        }

        return [
            'subscription' => $subscription,
            'tenants' => $tenants,
            'plans' => $plans->filter()->values(),
            'currencies' => SubscriptionPlanController::CURRENCIES,
            'cycles' => SubscriptionPlanController::CYCLES,
            'statuses' => $subscription->exists ? self::STATUSES : ['active', 'trial'],
        ];
    }

    protected function validated(Request $request, bool $creating): array
    {
        $rules = [
            'plan_id' => ['required', Rule::exists('subscription_plans', 'id')->whereNull('deleted_at')],
            'status' => ['required', Rule::in($creating ? ['active', 'trial'] : self::STATUSES)],
            'custom_price' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'custom_price_per_user' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'billing_cycle' => ['nullable', Rule::in(array_keys(SubscriptionPlanController::CYCLES))],
            'currency' => ['nullable', Rule::in(SubscriptionPlanController::CURRENCIES)],
            'custom_max_users' => ['nullable', 'integer', 'min:1'],
            'custom_max_courses' => ['nullable', 'integer', 'min:1'],
            'custom_max_storage_gb' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
        if ($creating) {
            $rules['tenant_id'] = ['required', 'exists:tenants,id'];
        }

        $data = $request->validate($rules);
        $data['discount_percent'] = $data['discount_percent'] ?? 0;

        return $data;
    }

    /** Keep the tenant record aligned with its subscription. */
    protected function syncTenant(TenantSubscription $subscription): void
    {
        $update = ['subscription_expires_at' => $subscription->expires_at];
        if ($maxUsers = $subscription->getEffectiveMaxUsers()) {
            $update['max_users'] = $maxUsers;
        }
        if ($subscription->plan) {
            $update['subscription_plan'] = $subscription->plan->slug;
        }
        $subscription->tenant->update($update);
    }
}
