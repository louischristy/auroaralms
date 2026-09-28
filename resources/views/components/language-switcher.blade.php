@php
    $locales = \App\Http\Middleware\SetLocale::SUPPORTED;
    $current = app()->getLocale();
@endphp
<div class="inline-block">
    @foreach($locales as $code => $label)
        <form id="locale-form-{{ $code }}" method="POST" action="{{ route('locale.update', $code) }}" class="hidden">@csrf</form>
    @endforeach
    <select aria-label="Language" class="text-sm border-gray-300 rounded-lg py-1"
            onchange="document.getElementById('locale-form-' + this.value).submit()">
        @foreach($locales as $code => $label)
            <option value="{{ $code }}" @selected($current === $code)>{{ $label }}</option>
        @endforeach
    </select>
</div>
