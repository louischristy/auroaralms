@extends('layouts.app')
@section('title', 'Add External Credential — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6 max-w-2xl">
    <div>
        <a href="{{ route('learn.credentials.index', ['tab' => 'external']) }}" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Credentials
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Add External Credential</h1>
        <p class="text-sm text-gray-500 mt-1">Track a certificate or credential earned on another platform.</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('learn.credentials.store') }}" enctype="multipart/form-data" class="card p-6 space-y-5">
        @csrf

        <div>
            <label for="platform" class="label">Platform</label>
            <select name="platform" id="platform" class="input" required>
                <option value="">Select a platform...</option>
                @foreach($platforms as $key => $platform)
                    <option value="{{ $key }}" {{ old('platform') === $key ? 'selected' : '' }}>{{ $platform['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="credential_name" class="label">Credential Name</label>
            <input type="text" name="credential_name" id="credential_name" class="input" value="{{ old('credential_name') }}" placeholder="e.g., Google Cybersecurity Certificate" required>
        </div>

        <div>
            <label for="credential_url" class="label">Credential URL</label>
            <input type="url" name="credential_url" id="credential_url" class="input" value="{{ old('credential_url') }}" placeholder="https://...">
            <p class="text-xs text-gray-400 mt-1">Link to your credential on the platform.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="credential_id" class="label">Credential ID</label>
                <input type="text" name="credential_id" id="credential_id" class="input" value="{{ old('credential_id') }}" placeholder="External ID or certificate number">
            </div>
            <div>
                <label for="issuer" class="label">Issuing Organization</label>
                <input type="text" name="issuer" id="issuer" class="input" value="{{ old('issuer') }}" placeholder="e.g., Google, Amazon">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="issued_at" class="label">Issue Date</label>
                <input type="date" name="issued_at" id="issued_at" class="input" value="{{ old('issued_at') }}">
            </div>
            <div>
                <label for="expires_at" class="label">Expiry Date</label>
                <input type="date" name="expires_at" id="expires_at" class="input" value="{{ old('expires_at') }}">
                <p class="text-xs text-gray-400 mt-1">Leave blank if it does not expire.</p>
            </div>
        </div>

        <div>
            <label for="status" class="label">Status</label>
            <select name="status" id="status" class="input" required>
                @foreach($statuses as $key => $label)
                    <option value="{{ $key }}" {{ old('status', 'active') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="certificate_file" class="label">Certificate File</label>
            <input type="file" name="certificate_file" id="certificate_file" class="input" accept=".pdf,.jpg,.jpeg,.png">
            <p class="text-xs text-gray-400 mt-1">Upload a PDF or image of your certificate (max 10 MB).</p>
        </div>

        <div>
            <label for="notes" class="label">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="input" placeholder="Any additional details about this credential...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="btn-primary">Save Credential</button>
            <a href="{{ route('learn.credentials.index', ['tab' => 'external']) }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection
