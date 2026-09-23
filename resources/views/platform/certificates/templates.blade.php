@extends('layouts.app')
@section('title', 'Certificate Templates — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Certificate Templates</h1>
            <p class="text-sm text-gray-500 mt-1">Design certificate templates for tenants. Set a default or let tenants choose their own.</p>
        </div>
        <a href="{{ route('platform.certificates.templates.create') }}" class="btn-primary">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">{{ session('error') }}</div>
    @endif

    @if($templates->isEmpty())
        <div class="card p-12 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No templates yet</h3>
            <p class="text-sm text-gray-500 mb-4">Create your first certificate template to customise how completion certificates look.</p>
            <a href="{{ route('platform.certificates.templates.create') }}" class="btn-primary">Create Template</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($templates as $tpl)
                <div class="card p-5 {{ !$tpl->is_active ? 'opacity-60' : '' }}">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                                {{ $tpl->name }}
                                @if($tpl->is_default)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">Default</span>
                                @endif
                                @if(!$tpl->is_active)
                                    <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Inactive</span>
                                @endif
                            </h3>
                            @if($tpl->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $tpl->description }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Config summary --}}
                    <div class="flex flex-wrap gap-2 text-xs text-gray-500 mb-4">
                        @php $cfg = $tpl->getFullConfig(); @endphp
                        <span class="px-2 py-1 bg-gray-50 rounded">{{ ucfirst($cfg['border_style'] ?? 'classic') }} border</span>
                        <span class="px-2 py-1 bg-gray-50 rounded">{{ ucfirst($cfg['font_style'] ?? 'classic') }} font</span>
                        @if($cfg['show_score'] ?? false)<span class="px-2 py-1 bg-green-50 text-green-700 rounded">Score</span>@endif
                        @if($cfg['show_signatures'] ?? false)<span class="px-2 py-1 bg-green-50 text-green-700 rounded">Signatures ({{ count($cfg['signatures'] ?? []) }})</span>@endif
                        @if(!empty($cfg['issuing_authority']))<span class="px-2 py-1 bg-green-50 text-green-700 rounded">Authority</span>@endif
                        @if(!empty($cfg['footer_text']))<span class="px-2 py-1 bg-green-50 text-green-700 rounded">Footer</span>@endif
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <a href="{{ route('platform.certificates.templates.edit', $tpl->id) }}" class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                        <span class="text-gray-300">|</span>
                        <a href="{{ route('platform.certificates.templates.preview', $tpl->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">Preview</a>
                        <span class="text-gray-300">|</span>
                        <form method="POST" action="{{ route('platform.certificates.templates.toggle', $tpl->id) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-gray-600 hover:text-gray-800 font-medium">{{ $tpl->is_active ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                        @if(!$tpl->is_default)
                            <span class="text-gray-300">|</span>
                            <form method="POST" action="{{ route('platform.certificates.templates.destroy', $tpl->id) }}" class="inline" onsubmit="return confirm('Delete this template?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
