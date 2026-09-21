@extends('layouts.app')
@section('title', 'Assign Users — ' . $course->title . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Assign Users to Course</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $course->title }}</p>
        </div>
        <a href="{{ route('manage.courses.edit', $course) }}" class="btn-outline">Back to Course</a>
    </div>

    @php
        // Build department-to-user mapping for Alpine
        $deptUserMap = [];
        foreach ($users as $u) {
            if ($u->department_id) {
                $deptUserMap[$u->department_id][] = $u->id;
            }
        }
    @endphp

    <form method="POST" action="{{ route('manage.courses.assign-users.save', $course) }}" class="space-y-6"
          x-data="{
              selectedUsers: {{ json_encode(array_values($assignedUserIds)) }},
              searchTerm: '',
              deptUsers: {{ json_encode($deptUserMap) }},
              toggleUser(id) {
                  const idx = this.selectedUsers.indexOf(id);
                  if (idx > -1) { this.selectedUsers.splice(idx, 1); }
                  else { this.selectedUsers.push(id); }
              },
              isSelected(id) { return this.selectedUsers.includes(id); },
              selectDept(deptId) {
                  const uids = this.deptUsers[deptId] || [];
                  uids.forEach(uid => {
                      if (!this.selectedUsers.includes(uid)) { this.selectedUsers.push(uid); }
                  });
              },
              matchesSearch(name, email) {
                  if (!this.searchTerm) return true;
                  const s = this.searchTerm.toLowerCase();
                  return name.toLowerCase().includes(s) || email.toLowerCase().includes(s);
              }
          }">
        @csrf

        {{-- Assignment Options --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-sm font-semibold text-gray-700">Assignment Options</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="due_date" class="label">Due Date (optional)</label>
                    <input type="date" id="due_date" name="due_date" class="input" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 pb-2">
                        <input type="checkbox" name="is_mandatory" value="1">
                        <span class="text-sm text-gray-700">Mandatory assignment</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Quick Assign by Department --}}
        @if($departments->isNotEmpty())
        <div class="card p-6 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Quick Assign by Department</h3>
            <p class="text-xs text-gray-500">Click a department to select all its active users.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($departments as $dept)
                    <button type="button" x-on:click="selectDept({{ $dept->id }})"
                            class="px-3 py-1.5 text-xs font-medium rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                        {{ $dept->name }}
                        @if(isset($dept->tenant))
                            <span class="text-blue-400">({{ $dept->tenant->name }})</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
        @endif

        {{-- User Selection --}}
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">
                    Select Users
                    <span class="text-gray-400 font-normal" x-text="'(' + selectedUsers.length + ' selected)'"></span>
                </h3>
                <input type="text" x-model="searchTerm" placeholder="Search users..." class="input w-64 text-sm">
            </div>
            <div class="max-h-96 overflow-y-auto divide-y divide-gray-50">
                @foreach($users as $u)
                    <label class="flex items-center gap-3 px-6 py-3 hover:bg-gray-50 cursor-pointer"
                           x-show="matchesSearch({{ json_encode($u->name) }}, {{ json_encode($u->email) }})">
                        <input type="checkbox" name="user_ids[]" value="{{ $u->id }}"
                               x-bind:checked="isSelected({{ $u->id }})"
                               x-on:change="toggleUser({{ $u->id }})"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $u->email }}
                                @if(isset($u->tenant))
                                    &middot; {{ $u->tenant->name }}
                                @endif
                            </p>
                        </div>
                        <span class="text-xs text-gray-400">{{ $u->department->name ?? '' }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">Save Assignments</button>
            <a href="{{ route('manage.courses.edit', $course) }}" class="btn-outline">Cancel</a>
        </div>
    </form>
</div>
@endsection
