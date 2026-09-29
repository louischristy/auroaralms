@php $a = $announcement ?? null; @endphp
@csrf

@if(!empty($tenants))
    <div>
        <label for="tenant_id" class="label">Organization *</label>
        <select id="tenant_id" name="tenant_id" class="input" required>
            <option value="">Select Tenant</option>
            @foreach($tenants as $tenant)
                <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>{{ $tenant->name }}</option>
            @endforeach
        </select>
        @error('tenant_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
@endif

<div>
    <label for="title" class="label">Title *</label>
    <input type="text" id="title" name="title" value="{{ old('title', $a?->title) }}" required maxlength="255" class="input" placeholder="e.g. New training module now available">
    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="content" class="label">Content *</label>
    <textarea id="content" name="content" rows="8" required class="input" placeholder="Write your announcement...">{{ old('content', $a?->content) }}</textarea>
    @error('content')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="type" class="label">Type *</label>
        <select id="type" name="type" class="input" required>
            @foreach($types as $t)
                <option value="{{ $t }}" {{ old('type', $a?->type ?? 'info') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="target_audience" class="label">Target Audience *</label>
        <select id="target_audience" name="target_audience" class="input" required>
            @foreach($audiences as $aud)
                <option value="{{ $aud }}" {{ old('target_audience', $a?->target_audience ?? 'all') === $aud ? 'selected' : '' }}>
                    {{ $aud === 'all' ? 'Everyone' : ucfirst($aud) }}
                </option>
            @endforeach
        </select>
        @error('target_audience')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="target_department_id" class="label">Department <span class="text-gray-400 font-normal">(optional)</span></label>
        <select id="target_department_id" name="target_department_id" class="input">
            <option value="">All departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}" {{ (string) old('target_department_id', $a?->target_department_id) === (string) $dept->id ? 'selected' : '' }}>
                    {{ $dept->name }}@if($dept->relationLoaded('tenant') && $dept->tenant) ({{ $dept->tenant->name }})@endif
                </option>
            @endforeach
        </select>
        @error('target_department_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="expires_at" class="label">Expires On <span class="text-gray-400 font-normal">(optional)</span></label>
        <input type="date" id="expires_at" name="expires_at" value="{{ old('expires_at', $a?->expires_at?->format('Y-m-d')) }}" class="input">
        @error('expires_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
    <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
        <input type="checkbox" name="is_pinned" value="1" class="mt-0.5 rounded border-gray-300" {{ old('is_pinned', $a?->is_pinned) ? 'checked' : '' }}>
        <span>
            <span class="block text-sm font-medium text-gray-900">Pin to top</span>
            <span class="block text-xs text-gray-500">Pinned announcements appear first for learners.</span>
        </span>
    </label>
    <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
        <input type="checkbox" name="is_published" value="1" class="mt-0.5 rounded border-gray-300" {{ old('is_published', $a?->is_published) ? 'checked' : '' }}>
        <span>
            <span class="block text-sm font-medium text-gray-900">Publish</span>
            <span class="block text-xs text-gray-500">Leave unchecked to save as a draft.</span>
        </span>
    </label>
</div>
