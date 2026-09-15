@extends('layouts.app')
@section('title', 'Create Tenant — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Create Tenant</h1>
        <p class="text-sm text-gray-500 mt-1">Add a new client organization to the platform.</p>
    </div>

    <form method="POST" action="{{ route('platform.tenants.store') }}" class="card p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="name" class="label">Organization Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required class="input">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="slug" class="label">Slug (auto-generated if empty)</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" class="input">
                @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="domain" class="label">Custom Domain (optional)</label>
            <input type="text" id="domain" name="domain" value="{{ old('domain') }}" placeholder="training.client.com" class="input">
            @error('domain')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="primary_color" class="label">Primary Color</label>
                <input type="color" id="primary_color" name="primary_color" value="{{ old('primary_color', '#2B4C7E') }}" class="input h-10">
            </div>
            <div>
                <label for="accent_color" class="label">Accent Color</label>
                <input type="color" id="accent_color" name="accent_color" value="{{ old('accent_color', '#5BC0EB') }}" class="input h-10">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="max_users" class="label">Max Users *</label>
                <input type="number" id="max_users" name="max_users" value="{{ old('max_users', 100) }}" min="1" required class="input">
            </div>
            <div>
                <label for="subscription_plan" class="label">Subscription Plan *</label>
                <select id="subscription_plan" name="subscription_plan" required class="input">
                    <option value="starter" {{ old('subscription_plan') === 'starter' ? 'selected' : '' }}>Starter</option>
                    <option value="standard" {{ old('subscription_plan', 'standard') === 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="professional" {{ old('subscription_plan') === 'professional' ? 'selected' : '' }}>Professional</option>
                    <option value="enterprise" {{ old('subscription_plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
        </div>

        <hr class="border-gray-200">
        <h3 class="text-lg font-medium text-gray-800">Initial Client Admin</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="admin_name" class="label">Admin Name *</label>
                <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required class="input">
                @error('admin_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="admin_email" class="label">Admin Email *</label>
                <input type="email" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required class="input">
                @error('admin_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Create Tenant</button>
            <a href="{{ route('platform.tenants.index') }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
