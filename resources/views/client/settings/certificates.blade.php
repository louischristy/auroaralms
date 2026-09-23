@extends('layouts.app')
@section('title', 'Certificate Settings — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Certificate Template</h1>
        <p class="text-sm text-gray-500 mt-1">Choose which certificate design your employees receive upon course completion.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('manage.settings.certificates.update') }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Default / no selection --}}
            <label class="card p-5 cursor-pointer border-2 transition-colors {{ !$selectedId ? 'border-blue-500 bg-blue-50/30' : 'border-transparent hover:border-gray-200' }}">
                <input type="radio" name="certificate_template_id" value="" class="sr-only" {{ !$selectedId ? 'checked' : '' }}>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center {{ !$selectedId ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <h3 class="font-semibold text-gray-900">Platform Default</h3>
                </div>
                <p class="text-sm text-gray-500">Use whichever template the platform administrator has set as default.</p>
            </label>

            @foreach($templates as $tpl)
                @php $cfg = $tpl->getFullConfig(); @endphp
                <label class="card p-5 cursor-pointer border-2 transition-colors {{ $selectedId == $tpl->id ? 'border-blue-500 bg-blue-50/30' : 'border-transparent hover:border-gray-200' }}">
                    <input type="radio" name="certificate_template_id" value="{{ $tpl->id }}" class="sr-only" {{ $selectedId == $tpl->id ? 'checked' : '' }}>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold text-gray-900">{{ $tpl->name }}</h3>
                        @if($tpl->is_default)
                            <span class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">Default</span>
                        @endif
                    </div>
                    @if($tpl->description)
                        <p class="text-sm text-gray-500 mb-3">{{ $tpl->description }}</p>
                    @endif
                    <div class="flex flex-wrap gap-1.5 text-xs text-gray-500 mb-3">
                        <span class="px-2 py-0.5 bg-gray-50 rounded">{{ ucfirst($cfg['border_style']) }}</span>
                        <span class="px-2 py-0.5 bg-gray-50 rounded">{{ ucfirst($cfg['font_style']) }}</span>
                        @if($cfg['show_score'])<span class="px-2 py-0.5 bg-green-50 text-green-700 rounded">Score</span>@endif
                        @if($cfg['show_signatures'])<span class="px-2 py-0.5 bg-green-50 text-green-700 rounded">Signatures</span>@endif
                    </div>
                    <a href="{{ route('manage.settings.certificates.preview', $tpl->id) }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Preview →</a>
                </label>
            @endforeach
        </div>

        <div class="mt-6">
            <button type="submit" class="btn-primary">Save Selection</button>
        </div>
    </form>
</div>
@endsection
