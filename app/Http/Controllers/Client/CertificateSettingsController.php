<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use App\Models\Tenant;
use Illuminate\Http\Request;

class CertificateSettingsController extends Controller
{
    public function index()
    {
        $tenant = app('current_tenant');

        // Available templates: platform templates + tenant's own
        $templates = CertificateTemplate::withoutTenantScope()
            ->where('is_active', true)
            ->where(function ($q) use ($tenant) {
                $q->whereNull('tenant_id')
                  ->orWhere('tenant_id', $tenant->id);
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $selectedId = $tenant->selected_certificate_template_id;

        return view('client.settings.certificates', compact('templates', 'selectedId'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'certificate_template_id' => 'nullable|exists:certificate_templates,id',
        ]);

        $tenant = app('current_tenant');
        $tenant->update([
            'selected_certificate_template_id' => $request->certificate_template_id,
        ]);

        return back()->with('success', 'Certificate template updated.');
    }

    public function preview(int $id)
    {
        $tenant = app('current_tenant');
        $template = CertificateTemplate::withoutTenantScope()
            ->where('is_active', true)
            ->where(function ($q) use ($tenant) {
                $q->whereNull('tenant_id')
                  ->orWhere('tenant_id', $tenant->id);
            })
            ->findOrFail($id);

        $config = $template->getFullConfig();
        $branding = view()->shared('branding', []);

        $sample = (object) [
            'user' => (object) ['name' => 'Jane Doe'],
            'course' => (object) ['title' => 'Phishing Awareness Fundamentals', 'duration_minutes' => 45],
            'certificate_number' => 'CERT-ABCD-20240915-EFGH',
            'score' => 92,
            'issued_at' => now(),
            'expires_at' => now()->addYear(),
        ];

        return view('employee.certificates.pdf', [
            'certificate' => $sample,
            'branding' => $branding,
            'config' => $config,
            'isPreview' => true,
        ]);
    }
}
