<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 0; }
    body { margin: 0; padding: 40px; font-family: 'Helvetica', 'Arial', sans-serif; background: #fff; }
    .certificate { border: 3px solid {{ $branding['primary_color'] ?? '#2B4C7E' }}; padding: 50px; text-align: center; position: relative; min-height: 460px; }
    .certificate::before { content: ''; position: absolute; top: 8px; left: 8px; right: 8px; bottom: 8px; border: 1px solid {{ $branding['primary_color'] ?? '#2B4C7E' }}; }
    .header { font-size: 14px; color: #666; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 10px; }
    .title { font-size: 36px; color: {{ $branding['primary_color'] ?? '#2B4C7E' }}; font-weight: bold; margin: 10px 0; }
    .subtitle { font-size: 14px; color: #888; margin-bottom: 30px; }
    .recipient { font-size: 28px; color: #333; font-weight: bold; border-bottom: 2px solid {{ $branding['primary_color'] ?? '#2B4C7E' }}; display: inline-block; padding: 0 40px 8px; margin: 10px 0 20px; }
    .course { font-size: 18px; color: #555; margin: 15px 0; }
    .course strong { color: {{ $branding['primary_color'] ?? '#2B4C7E' }}; }
    .details { font-size: 12px; color: #888; margin-top: 30px; }
    .details table { margin: 0 auto; }
    .details td { padding: 4px 20px; text-align: left; }
    .details td:first-child { font-weight: bold; color: #666; }
    .logo { margin-bottom: 15px; font-size: 20px; font-weight: bold; color: {{ $branding['primary_color'] ?? '#2B4C7E' }}; }
    .score { display: inline-block; background: {{ $branding['primary_color'] ?? '#2B4C7E' }}; color: #fff; padding: 6px 20px; border-radius: 20px; font-size: 14px; font-weight: bold; margin: 10px 0; }
</style>
</head>
<body>
<div class="certificate">
    <div class="logo">{{ $branding['platform_name'] ?? 'Auroara LMS' }}</div>
    <div class="header">Certificate of Completion</div>
    <div class="title">Cybersecurity Awareness Training</div>
    <div class="subtitle">This is to certify that</div>
    <div class="recipient">{{ $certificate->user->name }}</div>
    <div class="course">has successfully completed<br><strong>{{ $certificate->course->title }}</strong></div>
    @if($certificate->score)
        <div class="score">Score: {{ $certificate->score }}%</div>
    @endif
    <div class="details">
        <table>
            <tr><td>Certificate No:</td><td>{{ $certificate->certificate_number }}</td></tr>
            <tr><td>Date Issued:</td><td>{{ $certificate->issued_at->format('F j, Y') }}</td></tr>
            @if($certificate->expires_at)
            <tr><td>Valid Until:</td><td>{{ $certificate->expires_at->format('F j, Y') }}</td></tr>
            @endif
        </table>
    </div>
</div>
</body>
</html>
