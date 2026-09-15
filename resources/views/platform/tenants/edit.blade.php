@extends('layouts.app')
@section('title', 'Edit Tenant — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Tenant</h1>
        <p class="text-sm text-gray-500 mt-1">Update this client organization's details.</p>
    </div>

    <form method="POST" action="{{ route('platform.tenants.update', $tenant) }}" class="card p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="label">Organization Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}" required class="input">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="label">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $tenant->slug) }}" class="input">
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="domain" class="label">Custom Domain (optional)</label>
            <input type="text" id="domain" name="domain" value="{{ old('domain', $tenant->domain) }}" placeholder="training.client.com" class="input">
            @error('domain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="primary_color" class="label">Primary Color</label>
                <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color ?? '#2B4C7E') }}" class="input h-10">
            </div>
            <div>
                <label for="accent_color" class="label">Accent Color</label>
                <input type="color" id="accent_color" name="accent_color" value="{{ old('accent_color', $tenant->accent_color ?? '#5BC0EB') }}" class="input h-10">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="max_users" class="label">Max Users *</label>
                <input type="number" id="max_users" name="max_users" value="{{ old('max_users', $tenant->max_users) }}" min="1" required class="input">
            </div>
            <div>
                <label for="subscription_plan" class="label">Subscription Plan *</label>
                <select id="subscription_plan" name="subscription_plan" required class="input">
                    @foreach(['starter', 'standard', 'professional', 'enterprise'] as $plan)
                        <option value="{{ $plan }}" {{ old('subscription_plan', $tenant->subscription_plan) === $plan ? 'selected' : '' }}>{{ ucfirst($plan) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Changes</button>
            <a href="{{ route('platform.tenants.show', $tenant) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
