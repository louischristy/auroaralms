@php
    $cfg = $config ?? [];
    $borderStyle = $cfg['border_style'] ?? 'classic';
    $borderColor = $cfg['border_color'] ?? ($branding['primary_color'] ?? '#2B4C7E');
    $accentColor = $cfg['accent_color'] ?? ($branding['secondary_color'] ?? '#3A7BD5');
    $bgColor = $cfg['background_color'] ?? '#ffffff';
    $fontStyle = $cfg['font_style'] ?? 'classic';
    $showScore = $cfg['show_score'] ?? true;
    $showLogo = $cfg['show_logo'] ?? true;
    $showCertNum = $cfg['show_certificate_number'] ?? true;
    $showExpiry = $cfg['show_expiry'] ?? true;
    $showDateIssued = $cfg['show_date_issued'] ?? true;
    $showDuration = $cfg['show_course_duration'] ?? false;
    $showSignatures = $cfg['show_signatures'] ?? false;
    $signatures = $cfg['signatures'] ?? [];
    $issuingAuthority = $cfg['issuing_authority'] ?? '';
    $issuingAuthorityTitle = $cfg['issuing_authority_title'] ?? '';
    $footerText = $cfg['footer_text'] ?? '';
    $customTitle = $cfg['custom_title'] ?? 'Certificate of Completion';
    $customSubtitle = $cfg['custom_subtitle'] ?? 'Cybersecurity Awareness Training';
    $decorative = $cfg['decorative_elements'] ?? 'corners';

    $fontFamily = match($fontStyle) {
        'modern' => "'Helvetica', 'Arial', sans-serif",
        'elegant' => "'Georgia', 'Times New Roman', serif",
        default => "'Times New Roman', 'Georgia', serif",
    };
    $headingFont = match($fontStyle) {
        'elegant' => "'Georgia', cursive, serif",
        default => $fontFamily,
    };
@endphp
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; size: A4 landscape; }
    body {
        margin: 0;
        padding: 30px;
        font-family: {!! $fontFamily !!};
        background: {{ $bgColor }};
        color: #333;
    }

    .certificate {
        position: relative;
        padding: 50px 60px;
        text-align: center;
        min-height: 460px;
        @if($borderStyle === 'classic')
            border: 3px solid {{ $borderColor }};
        @elseif($borderStyle === 'modern')
            border: 2px solid {{ $borderColor }};
            border-radius: 8px;
        @elseif($borderStyle === 'ornate')
            border: 4px double {{ $borderColor }};
        @endif
    }
    @if($borderStyle === 'classic')
    .certificate::before {
        content: '';
        position: absolute;
        top: 8px; left: 8px; right: 8px; bottom: 8px;
        border: 1px solid {{ $borderColor }};
    }
    @endif

    /* Decorative corners */
    @if($decorative === 'corners')
    .corner { position: absolute; width: 40px; height: 40px; }
    .corner svg { width: 40px; height: 40px; }
    .corner-tl { top: 15px; left: 15px; }
    .corner-tr { top: 15px; right: 15px; transform: scaleX(-1); }
    .corner-bl { bottom: 15px; left: 15px; transform: scaleY(-1); }
    .corner-br { bottom: 15px; right: 15px; transform: scale(-1, -1); }
    @endif

    /* Seal */
    @if($decorative === 'seal')
    .seal {
        position: absolute;
        bottom: 30px; right: 50px;
        width: 80px; height: 80px;
        border: 3px solid {{ $borderColor }};
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: bold;
        color: {{ $borderColor }};
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    @endif

    /* Ribbon */
    @if($decorative === 'ribbon')
    .ribbon {
        background: {{ $borderColor }};
        color: #fff;
        padding: 6px 30px;
        font-size: 11px;
        letter-spacing: 3px;
        text-transform: uppercase;
        display: inline-block;
        margin-bottom: 15px;
    }
    @endif

    .logo {
        margin-bottom: 10px;
        font-size: 20px;
        font-weight: bold;
        color: {{ $borderColor }};
    }
    .header {
        font-size: 12px;
        color: #888;
        letter-spacing: 4px;
        text-transform: uppercase;
        margin-bottom: 8px;
    }
    .title {
        font-size: 32px;
        color: {{ $borderColor }};
        font-weight: bold;
        margin: 8px 0;
        font-family: {!! $headingFont !!};
    }
    .subtitle {
        font-size: 13px;
        color: #888;
        margin-bottom: 25px;
    }
    .recipient {
        font-size: 26px;
        color: #333;
        font-weight: bold;
        border-bottom: 2px solid {{ $accentColor }};
        display: inline-block;
        padding: 0 40px 8px;
        margin: 8px 0 18px;
        font-family: {!! $headingFont !!};
    }
    .course {
        font-size: 16px;
        color: #555;
        margin: 12px 0;
    }
    .course strong {
        color: {{ $borderColor }};
    }
    .score {
        display: inline-block;
        background: {{ $accentColor }};
        color: #fff;
        padding: 5px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: bold;
        margin: 8px 0;
    }
    .duration {
        font-size: 12px;
        color: #999;
        margin: 5px 0;
    }
    .details {
        font-size: 11px;
        color: #888;
        margin-top: 20px;
    }
    .details table {
        margin: 0 auto;
    }
    .details td {
        padding: 3px 15px;
        text-align: left;
    }
    .details td:first-child {
        font-weight: bold;
        color: #666;
    }

    /* Signatures */
    .signatures {
        margin-top: 30px;
        display: flex;
        justify-content: center;
        gap: 60px;
    }
    .signature-block {
        text-align: center;
        min-width: 150px;
    }
    .signature-line {
        border-top: 1px solid #999;
        margin-top: 35px;
        padding-top: 5px;
    }
    .signature-name {
        font-size: 13px;
        font-weight: bold;
        color: #333;
    }
    .signature-title {
        font-size: 11px;
        color: #888;
    }

    /* Authority */
    .authority {
        margin-top: 15px;
        font-size: 12px;
        color: #666;
    }
    .authority-name {
        font-weight: bold;
        color: {{ $borderColor }};
    }
    .authority-title {
        font-size: 11px;
        color: #999;
    }

    .footer {
        margin-top: 15px;
        font-size: 10px;
        color: #aaa;
        font-style: italic;
    }

    @if(!empty($isPreview))
    .preview-badge {
        position: absolute;
        top: 20px; right: 20px;
        background: #ef4444;
        color: #fff;
        padding: 4px 12px;
        font-size: 11px;
        font-weight: bold;
        border-radius: 4px;
        letter-spacing: 1px;
        z-index: 10;
    }
    @endif
</style>
</head>
<body>
<div class="certificate">
    @if(!empty($isPreview))
        <div class="preview-badge">PREVIEW</div>
    @endif

    @if($decorative === 'corners')
        <div class="corner corner-tl"><svg viewBox="0 0 40 40" fill="none"><path d="M0 40V0h40" stroke="{{ $borderColor }}" stroke-width="2"/><path d="M0 30V10c0-5.5 4.5-10 10-10h20" stroke="{{ $accentColor }}" stroke-width="1" opacity="0.5"/></svg></div>
        <div class="corner corner-tr"><svg viewBox="0 0 40 40" fill="none"><path d="M0 40V0h40" stroke="{{ $borderColor }}" stroke-width="2"/><path d="M0 30V10c0-5.5 4.5-10 10-10h20" stroke="{{ $accentColor }}" stroke-width="1" opacity="0.5"/></svg></div>
        <div class="corner corner-bl"><svg viewBox="0 0 40 40" fill="none"><path d="M0 40V0h40" stroke="{{ $borderColor }}" stroke-width="2"/><path d="M0 30V10c0-5.5 4.5-10 10-10h20" stroke="{{ $accentColor }}" stroke-width="1" opacity="0.5"/></svg></div>
        <div class="corner corner-br"><svg viewBox="0 0 40 40" fill="none"><path d="M0 40V0h40" stroke="{{ $borderColor }}" stroke-width="2"/><path d="M0 30V10c0-5.5 4.5-10 10-10h20" stroke="{{ $accentColor }}" stroke-width="1" opacity="0.5"/></svg></div>
    @endif

    @if($decorative === 'seal')
        <div class="seal">Certified</div>
    @endif

    @if($showLogo)
        <div class="logo">{{ $branding['platform_name'] ?? 'Auroara LMS' }}</div>
    @endif

    @if($decorative === 'ribbon')
        <div class="ribbon">{{ $customTitle }}</div>
    @else
        <div class="header">{{ $customTitle }}</div>
    @endif

    <div class="title">{{ $customSubtitle }}</div>
    <div class="subtitle">This is to certify that</div>
    <div class="recipient">{{ $certificate->user->name }}</div>
    <div class="course">has successfully completed<br><strong>{{ $certificate->course->title }}</strong></div>

    @if($showScore && $certificate->score)
        <div class="score">Score: {{ $certificate->score }}%</div>
    @endif

    @if($showDuration && isset($certificate->course->duration_minutes) && $certificate->course->duration_minutes)
        <div class="duration">Duration: {{ $certificate->course->duration_minutes }} minutes</div>
    @endif

    <div class="details">
        <table>
            @if($showCertNum)
                <tr><td>Certificate No:</td><td>{{ $certificate->certificate_number }}</td></tr>
            @endif
            @if($showDateIssued)
                <tr><td>Date Issued:</td><td>{{ $certificate->issued_at->format('F j, Y') }}</td></tr>
            @endif
            @if($showExpiry && $certificate->expires_at)
                <tr><td>Valid Until:</td><td>{{ $certificate->expires_at->format('F j, Y') }}</td></tr>
            @endif
        </table>
    </div>

    @if($showSignatures && count($signatures) > 0)
        <div class="signatures">
            @foreach($signatures as $sig)
                @if(!empty($sig['name']) || !empty($sig['title']))
                    <div class="signature-block">
                        <div class="signature-line">
                            <div class="signature-name">{{ $sig['name'] ?? '' }}</div>
                            <div class="signature-title">{{ $sig['title'] ?? '' }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    @if(!empty($issuingAuthority))
        <div class="authority">
            <div class="authority-name">{{ $issuingAuthority }}</div>
            @if(!empty($issuingAuthorityTitle))
                <div class="authority-title">{{ $issuingAuthorityTitle }}</div>
            @endif
        </div>
    @endif

    @if(!empty($footerText))
        <div class="footer">{{ $footerText }}</div>
    @endif
</div>
</body>
</html>
