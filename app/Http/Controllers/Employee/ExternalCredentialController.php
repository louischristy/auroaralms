<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ExternalCredential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExternalCredentialController extends Controller
{
    public function index()
    {
        $certificates = Certificate::where('user_id', Auth::id())
            ->with('course')
            ->orderByDesc('issued_at')
            ->get();

        $externalCredentials = ExternalCredential::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('employee.credentials.index', compact('certificates', 'externalCredentials'));
    }

    public function create()
    {
        $platforms = ExternalCredential::PLATFORMS;
        $statuses = ExternalCredential::STATUSES;

        return view('employee.credentials.create', compact('platforms', 'statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|in:' . implode(',', array_keys(ExternalCredential::PLATFORMS)),
            'credential_name' => 'required|string|max:255',
            'credential_url' => 'nullable|url|max:255',
            'credential_id' => 'nullable|string|max:255',
            'issuer' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:issued_at',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'status' => 'required|string|in:' . implode(',', array_keys(ExternalCredential::STATUSES)),
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('certificate_file')) {
            $validated['certificate_file'] = $request->file('certificate_file')
                ->store('credentials', 'local');
        }

        $validated['user_id'] = Auth::id();

        ExternalCredential::create($validated);

        return redirect()->route('learn.credentials.index')
            ->with('success', 'External credential added successfully.');
    }

    public function edit(ExternalCredential $credential)
    {
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        $platforms = ExternalCredential::PLATFORMS;
        $statuses = ExternalCredential::STATUSES;

        return view('employee.credentials.edit', compact('credential', 'platforms', 'statuses'));
    }

    public function update(Request $request, ExternalCredential $credential)
    {
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'platform' => 'required|string|in:' . implode(',', array_keys(ExternalCredential::PLATFORMS)),
            'credential_name' => 'required|string|max:255',
            'credential_url' => 'nullable|url|max:255',
            'credential_id' => 'nullable|string|max:255',
            'issuer' => 'nullable|string|max:255',
            'issued_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:issued_at',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'status' => 'required|string|in:' . implode(',', array_keys(ExternalCredential::STATUSES)),
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($request->hasFile('certificate_file')) {
            // Delete old file
            if ($credential->certificate_file) {
                Storage::disk('local')->delete($credential->certificate_file);
            }
            $validated['certificate_file'] = $request->file('certificate_file')
                ->store('credentials', 'local');
        }

        $credential->update($validated);

        return redirect()->route('learn.credentials.index')
            ->with('success', 'External credential updated successfully.');
    }

    public function destroy(ExternalCredential $credential)
    {
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        if ($credential->certificate_file) {
            Storage::disk('local')->delete($credential->certificate_file);
        }

        $credential->delete();

        return redirect()->route('learn.credentials.index')
            ->with('success', 'External credential deleted successfully.');
    }

    public function download(ExternalCredential $credential)
    {
        if ($credential->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$credential->certificate_file || !Storage::disk('local')->exists($credential->certificate_file)) {
            abort(404, 'Certificate file not found.');
        }

        return Storage::disk('local')->download($credential->certificate_file);
    }
}
