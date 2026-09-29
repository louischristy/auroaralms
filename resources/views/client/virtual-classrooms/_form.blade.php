@php
    $s = $session ?? null;
    // Show the stored time in the session's own timezone
    $scheduledValue = old('scheduled_at', $s?->scheduled_at?->copy()->timezone($s->timezone)->format('Y-m-d\TH:i'));
@endphp
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
    <input type="text" id="title" name="title" value="{{ old('title', $s?->title) }}" required maxlength="255" class="input" placeholder="e.g., Leadership Skills Workshop">
    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="description" class="label">Description</label>
    <textarea id="description" name="description" rows="3" class="input" placeholder="What will this session cover?">{{ old('description', $s?->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

{{-- Platform picker with icons --}}
<div>
    <label class="label">Platform *</label>
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3" x-data="{ platform: @js(old('platform', $s?->platform ?? 'zoom')) }">
        @foreach($platforms as $value => $label)
            <label class="cursor-pointer">
                <input type="radio" name="platform" value="{{ $value }}" class="sr-only peer" x-model="platform" {{ old('platform', $s?->platform ?? 'zoom') === $value ? 'checked' : '' }}>
                <span class="flex flex-col items-center gap-2 p-3 rounded-lg border-2 border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 peer-checked:border-primary peer-checked:bg-primary/5 peer-checked:text-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary">
                    @include('partials.platform-icon', ['platform' => $value, 'size' => 'w-10 h-10'])
                    {{ $label }}
                </span>
            </label>
        @endforeach
    </div>
    @error('platform')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="meeting_url" class="label">Meeting URL *</label>
    <input type="url" id="meeting_url" name="meeting_url" value="{{ old('meeting_url', $s?->meeting_url) }}" required class="input" placeholder="https://zoom.us/j/123456789">
    @error('meeting_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <div>
        <label for="meeting_id" class="label">Meeting ID</label>
        <input type="text" id="meeting_id" name="meeting_id" value="{{ old('meeting_id', $s?->meeting_id) }}" class="input">
        @error('meeting_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="passcode" class="label">Passcode</label>
        <input type="text" id="passcode" name="passcode" value="{{ old('passcode', $s?->passcode) }}" class="input" autocomplete="off">
        @error('passcode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="host_name" class="label">Host Name</label>
        <input type="text" id="host_name" name="host_name" value="{{ old('host_name', $s?->host_name) }}" class="input">
        @error('host_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <label for="scheduled_at" class="label">Date &amp; Time *</label>
        <input type="datetime-local" id="scheduled_at" name="scheduled_at" value="{{ $scheduledValue }}" required class="input">
        <p class="mt-1 text-xs text-gray-500">Entered in the timezone selected on the right.</p>
        @error('scheduled_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="timezone" class="label">Timezone *</label>
        <select id="timezone" name="timezone" class="input" required>
            @foreach($timezones as $tz => $label)
                <option value="{{ $tz }}" {{ old('timezone', $s?->timezone ?? 'Asia/Kuala_Lumpur') === $tz ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        @error('timezone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="duration_minutes" class="label">Duration (minutes) *</label>
        <input type="number" id="duration_minutes" name="duration_minutes" value="{{ old('duration_minutes', $s?->duration_minutes ?? 60) }}" min="5" max="1440" step="5" required class="input">
        @error('duration_minutes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="max_participants" class="label">Max Participants <span class="text-gray-400 font-normal">(blank = unlimited)</span></label>
        <input type="number" id="max_participants" name="max_participants" value="{{ old('max_participants', $s?->max_participants) }}" min="1" class="input">
        @error('max_participants')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="course_id" class="label">Linked Course <span class="text-gray-400 font-normal">(optional)</span></label>
    <select id="course_id" name="course_id" class="input">
        <option value="">Not linked to a course</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" {{ (string) old('course_id', $s?->course_id) === (string) $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500">Course-linked sessions are offered to learners enrolled in that course.</p>
    @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

{{-- Recurrence --}}
<div class="p-4 rounded-lg border border-gray-200 space-y-3"
     x-data="{ recurring: @js((bool) old('is_recurring', $s?->is_recurring ?? false)) }">
    <label class="flex items-start gap-3 cursor-pointer">
        <input type="checkbox" name="is_recurring" value="1" x-model="recurring" class="mt-0.5 rounded border-gray-300">
        <span>
            <span class="block text-sm font-medium text-gray-900">Recurring session</span>
            <span class="block text-xs text-gray-500">Marks this session as part of a repeating series.</span>
        </span>
    </label>
    <div x-show="recurring" x-cloak class="sm:w-64">
        <label for="recurrence_pattern" class="label">Repeats</label>
        <select id="recurrence_pattern" name="recurrence_pattern" class="input" :disabled="!recurring">
            @foreach($recurrence as $r)
                <option value="{{ $r }}" {{ old('recurrence_pattern', $s?->recurrence_pattern ?? 'weekly') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        @error('recurrence_pattern')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

@if($s)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="status" class="label">Status *</label>
            <select id="status" name="status" class="input" required>
                @foreach($statuses as $st)
                    <option value="{{ $st }}" {{ old('status', $s->status) === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="recording_url" class="label">Recording URL</label>
            <input type="url" id="recording_url" name="recording_url" value="{{ old('recording_url', $s->recording_url) }}" class="input" placeholder="https://...">
            @error('recording_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
@endif
