<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionPlanController extends Controller
{
    public const MODULES = [
        'learning_paths' => 'Learning Paths',
        'surveys' => 'Surveys',
        'virtual_classroom' => 'Virtual Classroom',
        'gamification' => 'Gamification',
        'phishing_sim' => 'Phishing Simulation',
        'advanced_reports' => 'Advanced Reports',
        'ai_features' => 'AI Features',
    ];

    public const CURRENCIES = ['USD', 'MYR', 'SGD', 'AED', 'SAR'];
    public const CYCLES = ['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly', 'custom' => 'Custom'];
    public const FEATURES = [
        'custom_branding' => 'Custom Branding',
        'custom_domain' => 'Custom Domain',
        'api_access' => 'API Access',
        'sso_enabled' => 'Single Sign-On (SSO)',
        'priority_support' => 'Priority Support',
    ];

    public function index(Request $request)
    {
        $sortable = ['name', 'base_price', 'price_per_user', 'max_users', 'billing_cycle', 'sort_order', 'subscriptions_count'];
        $sort = in_array($request->query('sort'), $sortable, true) ? $request->query('sort') : 'sort_order';
        $dir = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        $plans = SubscriptionPlan::withCount([
            'subscriptions',
            'subscriptions as active_subscriptions_count' => fn ($q) => $q->whereIn('status', ['active', 'trial']),
        ])->orderBy($sort, $dir)->orderBy('name')->get();

        return view('platform.subscription-plans.index', [
            'plans' => $plans,
            'sort' => $sort,
            'dir' => $dir,
            'modules' => self::MODULES,
            'featureLabels' => self::FEATURES,
        ]);
    }

    public function create()
    {
        return view('platform.subscription-plans.create', $this->formData(new SubscriptionPlan([
            'billing_cycle' => 'monthly', 'currency' => 'USD', 'min_users' => 1,
            'is_active' => true, 'sort_order' => 0, 'trial_days' => 0,
        ])));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $plan = SubscriptionPlan::create($data);

        return redirect()->route('platform.subscription-plans.index')
            ->with('success', "Plan '{$plan->name}' created.");
    }

    public function edit($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        return view('platform.subscription-plans.edit', $this->formData($plan));
    }

    public function update(Request $request, $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update($this->validated($request));

        return redirect()->route('platform.subscription-plans.index')
            ->with('success', "Plan '{$plan->name}' updated.");
    }

    public function destroy($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        if ($plan->subscriptions()->whereIn('status', ['active', 'trial'])->exists()) {
            return back()->with('error', "Plan '{$plan->name}' has active subscriptions and cannot be deleted.");
        }

        $plan->delete();

        return redirect()->route('platform.subscription-plans.index')
            ->with('success', "Plan '{$plan->name}' deleted.");
    }

    public function duplicate($id)
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $copy = $plan->replicate();
        $copy->name = 'Copy of ' . $plan->name;
        $copy->slug = $this->uniqueSlug($copy->name);
        $copy->is_active = false;
        $copy->is_featured = false;
        $copy->save();

        return redirect()->route('platform.subscription-plans.edit', $copy->id)
            ->with('success', 'Plan duplicated. It is inactive until you activate it.');
    }

    protected function formData(SubscriptionPlan $plan): array
    {
        return [
            'plan' => $plan,
            'modules' => self::MODULES,
            'currencies' => self::CURRENCIES,
            'cycles' => self::CYCLES,
            'featureLabels' => self::FEATURES,
        ];
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'billing_cycle' => ['required', Rule::in(array_keys(self::CYCLES))],
            'trial_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'price_per_user' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['required', Rule::in(self::CURRENCIES)],
            'min_users' => ['required', 'integer', 'min:1'],
            'max_users' => ['nullable', 'integer', 'gte:min_users'],
            'max_courses' => ['nullable', 'integer', 'min:1'],
            'max_storage_gb' => ['nullable', 'integer', 'min:1'],
            'modules' => ['nullable', 'array'],
            'modules.*' => [Rule::in(array_keys(self::MODULES))],
            'sort_order' => ['nullable', 'integer'],
        ]);

        foreach (array_keys(self::FEATURES) as $flag) {
            $data[$flag] = $request->boolean($flag);
        }
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['modules'] = array_values($data['modules'] ?? []);
        $data['trial_days'] = (int) ($data['trial_days'] ?? 0);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'plan';
        $slug = $base;
        $i = 2;
        while (SubscriptionPlan::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
