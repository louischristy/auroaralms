<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())
            ->with('course')
            ->orderByDesc('issued_at')
            ->get();

        return view('employee.certificates.index', compact('certificates'));
    }

    public function download(Certificate $certificate)
    {
        if ($certificate->user_id !== Auth::id()) {
            abort(403);
        }

        $certificate->load(['user', 'course']);
        $branding = view()->shared('branding', []);

        $pdf = Pdf::loadView('employee.certificates.pdf', [
            'certificate' => $certificate,
            'branding' => $branding,
        ])->setPaper('A4', 'landscape');

        $filename = 'certificate-' . $certificate->certificate_number . '.pdf';

        return $pdf->stream($filename);
    }
}
