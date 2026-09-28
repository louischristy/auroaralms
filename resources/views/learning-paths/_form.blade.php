{{--
  Shared learning path form.
  Vars: $action, $method ('POST'|'PUT'), $path (nullable), $availableCourses, $selected (collection of {id,title}), $cancelUrl
--}}
@php
    $selectedIds = old('course_ids', collect($selected ?? [])->pluck('id')->all());
    $byId = $availableCourses->keyBy('id');
    $initialSelected = collect($selectedIds)->map(fn($id) => $byId->get((int) $id))->filter()->map(fn($c) => [
        'id' => $c->id, 'title' => $c->title, 'category' => $c->category ?? '', 'minutes' => (int) ($c->duration_minutes ?? 0),
    ])->values();
    $initialAvailable = $availableCourses->map(fn($c) => [
        'id' => $c->id, 'title' => $c->title, 'category' => $c->category ?? '', 'minutes' => (int) ($c->duration_minutes ?? 0),
    ])->values();
@endphp

<form method="POST" action="{{ $action }}" class="space-y-6"
      x-data="{
          all: {{ json_encode($initialAvailable) }},
          selected: {{ json_encode($initialSelected) }},
          search: '',
          dragIndex: null,
          overIndex: null,
          isSelected(id) { return this.selected.some(c => c.id === id); },
          get available() {
              const s = this.search.toLowerCase();
              return this.all.filter(c => !this.isSelected(c.id) && (!s || c.title.toLowerCase().includes(s)));
          },
          get totalMinutes() { return this.selected.reduce((t, c) => t + c.minutes, 0); },
          add(c) { if (!this.isSelected(c.id)) { this.selected.push(c); } },
          addAll() { this.available.forEach(c => this.selected.push(c)); },
          remove(i) { this.selected.splice(i, 1); },
          clear() { this.selected = []; },
          move(i, dir) {
              const j = i + dir;
              if (j < 0 || j >= this.selected.length) return;
              const item = this.selected.splice(i, 1)[0];
              this.selected.splice(j, 0, item);
          },
          onDragStart(i) { this.dragIndex = i; },
          onDragOver(i) { this.overIndex = i; },
          onDrop(i) {
              if (this.dragIndex === null || this.dragIndex === i) { this.reset(); return; }
              const item = this.selected.splice(this.dragIndex, 1)[0];
              this.selected.splice(i, 0, item);
              this.reset();
          },
          reset() { this.dragIndex = null; this.overIndex = null; }
      }">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Details --}}
    <div class="card p-6 space-y-5">
        <h3 class="text-sm font-semibold text-gray-700">Path Details</h3>

        <div>
            <label for="title" class="label">Title</label>
            <input type="text" id="title" name="title" required maxlength="255" class="input"
                   value="{{ old('title', $path->title ?? '') }}" placeholder="e.g. New Hire Security Foundations">
        </div>

        <div>
            <label for="description" class="label">Description</label>
            <textarea id="description" name="description" rows="3" class="input"
                      placeholder="What will learners achieve by completing this path?">{{ old('description', $path->description ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="difficulty" class="label">Difficulty</label>
                <select id="difficulty" name="difficulty" class="input">
                    @foreach(['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('difficulty', $path->difficulty ?? 'beginner') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            @if($path)
            <div>
                <label for="is_active" class="label">Status</label>
                <select id="is_active" name="is_active" class="input">
                    <option value="1" {{ old('is_active', $path->is_active) ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !old('is_active', $path->is_active) ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="hidden" name="is_sequential" value="0">
                <input type="checkbox" name="is_sequential" value="1" class="mt-0.5 rounded border-gray-300"
                       {{ old('is_sequential', $path->is_sequential ?? true) ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-medium text-gray-900">Sequential</span>
                    <span class="block text-xs text-gray-500">Learners must finish each course before the next unlocks.</span>
                </span>
            </label>
            <label class="flex items-start gap-3 p-4 rounded-lg border border-gray-200 hover:bg-gray-50 cursor-pointer">
                <input type="hidden" name="is_mandatory" value="0">
                <input type="checkbox" name="is_mandatory" value="1" class="mt-0.5 rounded border-gray-300"
                       {{ old('is_mandatory', $path->is_mandatory ?? false) ? 'checked' : '' }}>
                <span>
                    <span class="block text-sm font-medium text-gray-900">Mandatory</span>
                    <span class="block text-xs text-gray-500">Marks this path as required training.</span>
                </span>
            </label>
        </div>
    </div>

    {{-- Course selector --}}
    <div class="card">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
            <h3 class="text-sm font-semibold text-gray-700">Courses</h3>
            <p class="text-xs text-gray-500">
                <span x-text="selected.length"></span> selected &middot; approx.
                <span x-text="totalMinutes"></span> min
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0 lg:divide-x divide-gray-100">
            {{-- Available --}}
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Available</p>
                    <button type="button" class="text-xs text-blue-600 hover:underline" x-on:click="addAll()">Add all</button>
                </div>
                <input type="text" x-model="search" placeholder="Filter courses..." class="input text-sm mb-3">
                <ul class="space-y-2 max-h-96 overflow-y-auto">
                    <template x-for="c in available" x-bind:key="c.id">
                        <li class="flex items-center justify-between gap-3 p-3 rounded-lg border border-gray-200 hover:border-blue-300 hover:bg-blue-50/40">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="c.title"></p>
                                <p class="text-xs text-gray-400" x-text="c.category + (c.minutes ? ' · ' + c.minutes + ' min' : '')"></p>
                            </div>
                            <button type="button" x-on:click="add(c)" title="Add"
                                    class="shrink-0 p-1.5 rounded-md text-blue-600 hover:bg-blue-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </li>
                    </template>
                    <li x-show="available.length === 0" class="text-sm text-gray-400 text-center py-6">No more courses available.</li>
                </ul>
            </div>

            {{-- Selected --}}
            <div class="p-4 bg-gray-50/50">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">In this path (drag to reorder)</p>
                    <button type="button" class="text-xs text-red-600 hover:underline" x-on:click="clear()">Clear</button>
                </div>
                <ol class="space-y-2 max-h-[27rem] overflow-y-auto">
                    <template x-for="(c, i) in selected" x-bind:key="c.id">
                        <li draggable="true"
                            x-on:dragstart="onDragStart(i)"
                            x-on:dragover.prevent="onDragOver(i)"
                            x-on:drop.prevent="onDrop(i)"
                            x-on:dragend="reset()"
                            x-bind:class="{ 'opacity-40': dragIndex === i, 'ring-2 ring-blue-400': overIndex === i && dragIndex !== null && dragIndex !== i }"
                            class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 bg-white cursor-move">
                            <svg class="w-5 h-5 text-gray-300 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M7 4a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2zM7 9a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2zM7 14a1 1 0 110 2 1 1 0 010-2zm6 0a1 1 0 110 2 1 1 0 010-2z"/></svg>
                            <span class="w-6 h-6 shrink-0 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold flex items-center justify-center" x-text="i + 1"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate" x-text="c.title"></p>
                                <p class="text-xs text-gray-400" x-text="c.category + (c.minutes ? ' · ' + c.minutes + ' min' : '')"></p>
                            </div>
                            <input type="hidden" name="course_ids[]" x-bind:value="c.id">
                            <div class="flex items-center shrink-0">
                                <button type="button" x-on:click="move(i, -1)" x-bind:disabled="i === 0" title="Move up" class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" x-on:click="move(i, 1)" x-bind:disabled="i === selected.length - 1" title="Move down" class="p-1 text-gray-400 hover:text-gray-700 disabled:opacity-30">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button type="button" x-on:click="remove(i)" title="Remove" class="p-1 text-gray-400 hover:text-red-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </li>
                    </template>
                    <li x-show="selected.length === 0" class="text-sm text-gray-400 text-center py-10 border-2 border-dashed border-gray-200 rounded-lg">
                        Add courses from the left to build the path.
                    </li>
                </ol>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit" class="btn-primary" x-bind:disabled="selected.length === 0">{{ $path ? 'Save Changes' : 'Create Learning Path' }}</button>
        <a href="{{ $cancelUrl }}" class="btn-outline">Cancel</a>
    </div>
</form>
