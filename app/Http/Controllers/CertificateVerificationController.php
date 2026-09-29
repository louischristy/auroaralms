<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    public function verify()
    {
        return view('certificates.verify');
    }

    public function check(Request $request, ?string $code = null)
    {
        $code = $code ?? $request->input('code');

        if (!$code) {
            return redirect()->route('certificates.verify')
                ->with('error', 'Please enter a verification code.');
        }

        $code = strtoupper(trim($code));

        $certificate = Certificate::findByVerificationCode($code);

        if (!$certificate || !$certificate->is_public) {
            return view('certificates.verify', [
                'searched' => true,
                'code' => $code,
                'certificate' => null,
            ]);
        }

        $certificate->load(['user', 'course', 'course.tenant']);

        return view('certificates.verify', [
            'searched' => true,
            'code' => $code,
            'certificate' => $certificate,
        ]);
    }
}
