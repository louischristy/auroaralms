@extends('layouts.app')
@section('title', 'Team Members — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team Members</h1>
        <p class="text-sm text-gray-500 mt-1">Users in your department.</p>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Email</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $member->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $member->email }}</td>
                            <td class="px-4 py-3 text-gray-500">
                                @foreach($member->roles as $role)
                                    <span class="badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($member->is_active)
                                    <span class="badge-success">Active</span>
                                @else
                                    <span class="badge-danger">Inactive</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-gray-400">No team members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($members->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
