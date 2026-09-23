@extends('layouts.app')
@section('title', ($template ? 'Edit' : 'Create') . ' Certificate Template — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <a href="{{ route('platform.certificates.templates.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Certificate Templates
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $template ? 'Edit Template: ' . $template->name : 'Create Certificate Template' }}</h1>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700 text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $template ? route('platform.certificates.templates.update', $template->id) : route('platform.certificates.templates.store') }}"
          class="space-y-6">
        @csrf
        @if($template) @method('PUT') @endif

        {{-- Basic Info --}}
        <div class="card p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-800">Template Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="label">Template Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $template->name ?? '') }}" class="input" required placeholder="e.g. Classic Professional">
                </div>
                <div>
                    <label for="description" class="label">Description</label>
                    <input type="text" id="description" name="description" value="{{ old('description', $template->description ?? '') }}" class="input" placeholder="Brief description of this template style">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="hidden" name="is_default" value="0">
                <input type="checkbox" name="is_default" value="1" class="rounded border-gray-300 text-primary focus:ring-primary" {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }}>
                <span class="font-medium text-gray-700">Set as default template</span>
                <span class="text-gray-400">(used when tenants haven't chosen one)</span>
            </label>
        </div>

        {{-- Appearance --}}
        <div class="card p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-800">Appearance</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="border_style" class="label">Border Style</label>
                    <select id="border_style" name="config[border_style]" class="input">
                        @foreach(['classic' => 'Classic — Double-line formal border', 'modern' => 'Modern — Clean single-line', 'ornate' => 'Ornate — Decorative corner elements', 'minimal' => 'Minimal — No visible border'] as $val => $lbl)
                            <option value="{{ $val }}" {{ ($config['border_style'] ?? 'classic') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="font_style" class="label">Font Style</label>
                    <select id="font_style" name="config[font_style]" class="input">
                        @foreach(['classic' => 'Classic — Serif (Times-like)', 'modern' => 'Modern — Sans-serif (Clean)', 'elegant' => 'Elegant — Script headings'] as $val => $lbl)
                            <option value="{{ $val }}" {{ ($config['font_style'] ?? 'classic') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="decorative_elements" class="label">Decorative Elements</label>
                    <select id="decorative_elements" name="config[decorative_elements]" class="input">
                        @foreach(['none' => 'None', 'corners' => 'Corner flourishes', 'ribbon' => 'Ribbon accent', 'seal' => 'Seal / badge'] as $val => $lbl)
                            <option value="{{ $val }}" {{ ($config['decorative_elements'] ?? 'corners') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <label for="border_color" class="label">Border / Primary Colour</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="border_color_picker" value="{{ $config['border_color'] ?? '#2B4C7E' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('border_color').value=this.value">
                        <input type="text" id="border_color" name="config[border_color]" value="{{ old('config.border_color', $config['border_color'] ?? '#2B4C7E') }}" class="input" onchange="document.getElementById('border_color_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label for="accent_color" class="label">Accent Colour</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="accent_color_picker" value="{{ $config['accent_color'] ?? '#3A7BD5' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('accent_color').value=this.value">
                        <input type="text" id="accent_color" name="config[accent_color]" value="{{ old('config.accent_color', $config['accent_color'] ?? '#3A7BD5') }}" class="input" onchange="document.getElementById('accent_color_picker').value=this.value">
                    </div>
                </div>
                <div>
                    <label for="background_color" class="label">Background Colour</label>
                    <div class="flex items-center gap-2">
                        <input type="color" id="background_color_picker" value="{{ $config['background_color'] ?? '#ffffff' }}" class="w-10 h-10 rounded cursor-pointer border border-gray-300" onchange="document.getElementById('background_color').value=this.value">
                        <input type="text" id="background_color" name="config[background_color]" value="{{ old('config.background_color', $config['background_color'] ?? '#ffffff') }}" class="input" onchange="document.getElementById('background_color_picker').value=this.value">
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="card p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-800">Content</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="custom_title" class="label">Certificate Title</label>
                    <input type="text" id="custom_title" name="config[custom_title]" value="{{ old('config.custom_title', $config['custom_title'] ?? 'Certificate of Completion') }}" class="input" placeholder="Certificate of Completion">
                </div>
                <div>
                    <label for="custom_subtitle" class="label">Subtitle</label>
                    <input type="text" id="custom_subtitle" name="config[custom_subtitle]" value="{{ old('config.custom_subtitle', $config['custom_subtitle'] ?? 'Cybersecurity Awareness Training') }}" class="input" placeholder="Cybersecurity Awareness Training">
                </div>
            </div>

            {{-- Visibility toggles --}}
            <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Show / Hide Elements</h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach([
                        'show_score' => 'Quiz Score',
                        'show_logo' => 'Platform Logo',
                        'show_certificate_number' => 'Certificate Number',
                        'show_expiry' => 'Expiry Date',
                        'show_date_issued' => 'Date Issued',
                        'show_course_duration' => 'Course Duration',
                    ] as $key => $label)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="hidden" name="config[{{ $key }}]" value="0">
                            <input type="checkbox" name="config[{{ $key }}]" value="1"
                                   class="rounded border-gray-300 text-primary focus:ring-primary"
                                   {{ old('config.' . $key, $config[$key] ?? false) ? 'checked' : '' }}>
                            <span class="text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="footer_text" class="label">Footer Text</label>
                <input type="text" id="footer_text" name="config[footer_text]" value="{{ old('config.footer_text', $config['footer_text'] ?? '') }}" class="input" placeholder="e.g. This certificate is valid for 12 months from the date of issue">
                <p class="text-xs text-gray-400 mt-1">Displayed at the bottom of the certificate. Leave empty to hide.</p>
            </div>
        </div>

        {{-- Signatures & Authority --}}
        <div class="card p-6 space-y-5">
            <h3 class="text-lg font-semibold text-gray-800">Signatures & Issuing Authority</h3>

            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="hidden" name="config[show_signatures]" value="0">
                <input type="checkbox" name="config[show_signatures]" value="1"
                       class="rounded border-gray-300 text-primary focus:ring-primary"
                       {{ old('config.show_signatures', $config['show_signatures'] ?? false) ? 'checked' : '' }}>
                <span class="font-medium text-gray-700">Show signatures on certificate</span>
            </label>

            <div class="border border-gray-200 rounded-lg p-4 space-y-4">
                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Signatories (up to 3)</h4>
                @for($i = 0; $i < 3; $i++)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 {{ $i > 0 ? 'border-t border-gray-100 pt-4' : '' }}">
                        <div>
                            <label class="label">Signatory {{ $i + 1 }} — Name</label>
                            <input type="text" name="config[signatures][{{ $i }}][name]"
                                   value="{{ old('config.signatures.' . $i . '.name', $config['signatures'][$i]['name'] ?? '') }}"
                                   class="input" placeholder="e.g. Dr. Sarah Johnson">
                        </div>
                        <div>
                            <label class="label">Title / Position</label>
                            <input type="text" name="config[signatures][{{ $i }}][title]"
                                   value="{{ old('config.signatures.' . $i . '.title', $config['signatures'][$i]['title'] ?? '') }}"
                                   class="input" placeholder="e.g. Chief Information Security Officer">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="label">Signature Image</label>
                            @if(!empty($config['signatures'][$i]['image']))
                                <div class="flex items-center gap-3 mb-2">
                                    <img src="{{ $config['signatures'][$i]['image'] }}" alt="Signature" class="h-12 max-w-48 object-contain border border-gray-200 rounded bg-white p-1">
                                    <label class="flex items-center gap-1 text-sm text-gray-500">
                                        <input type="checkbox" name="remove_signature_image[{{ $i }}]" value="1" class="rounded border-gray-300">
                                        Remove
                                    </label>
                                </div>
                            @endif
                            <input type="file" name="signature_images[{{ $i }}]" accept="image/png,image/jpeg,image/svg+xml,image/webp" class="input text-sm">
                            <p class="text-xs text-gray-400 mt-1">Upload a signature image (PNG with transparent background recommended). Max 1MB.</p>
                        </div>
                    </div>
                @endfor
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="issuing_authority" class="label">Issuing Authority Name</label>
                    <input type="text" id="issuing_authority" name="config[issuing_authority]"
                           value="{{ old('config.issuing_authority', $config['issuing_authority'] ?? '') }}"
                           class="input" placeholder="e.g. Auroara Security Training Institute">
                    <p class="text-xs text-gray-400 mt-1">Organisation name shown on the certificate. Leave empty to use the platform name.</p>
                </div>
                <div>
                    <label for="issuing_authority_title" class="label">Authority Subtitle</label>
                    <input type="text" id="issuing_authority_title" name="config[issuing_authority_title]"
                           value="{{ old('config.issuing_authority_title', $config['issuing_authority_title'] ?? '') }}"
                           class="input" placeholder="e.g. Accredited Cybersecurity Training Provider">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary">{{ $template ? 'Update Template' : 'Create Template' }}</button>
            <a href="{{ route('platform.certificates.templates.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection
