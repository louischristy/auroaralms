<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CertificateTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateTemplateController extends Controller
{
    public function index()
    {
        $templates = CertificateTemplate::withoutTenantScope()
            ->whereNull('tenant_id')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        return view('platform.certificates.templates', compact('templates'));
    }

    public function create()
    {
        $template = null;
        $config = CertificateTemplate::defaultConfig();

        return view('platform.certificates.template-edit', compact('template', 'config'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_default' => 'boolean',
            'config' => 'required|array',
            'config.signatures' => 'nullable|array|max:3',
            'config.signatures.*.name' => 'nullable|string|max:255',
            'config.signatures.*.title' => 'nullable|string|max:255',
            'signature_images.*' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:1024',
        ]);

        // Clean config
        $config = $this->cleanConfig($validated['config']);
        $config = $this->handleSignatureImages($request, $config);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default'])) {
            CertificateTemplate::withoutTenantScope()
                ->whereNull('tenant_id')
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $template = CertificateTemplate::withoutGlobalScopes()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
            'config' => $config,
            'created_by' => Auth::id(),
            'tenant_id' => null,
        ]);

        return redirect()->route('platform.certificates.templates.index')
            ->with('success', "Template \"{$template->name}\" created successfully.");
    }

    public function edit(int $id)
    {
        $template = CertificateTemplate::withoutTenantScope()->findOrFail($id);
        $config = $template->getFullConfig();

        return view('platform.certificates.template-edit', compact('template', 'config'));
    }

    public function update(Request $request, int $id)
    {
        $template = CertificateTemplate::withoutTenantScope()->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_default' => 'boolean',
            'config' => 'required|array',
            'config.signatures' => 'nullable|array|max:3',
            'config.signatures.*.name' => 'nullable|string|max:255',
            'config.signatures.*.title' => 'nullable|string|max:255',
            'signature_images.*' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:1024',
        ]);

        $config = $this->cleanConfig($validated['config']);
        $config = $this->handleSignatureImages($request, $config, $template);

        if (!empty($validated['is_default']) && !$template->is_default) {
            CertificateTemplate::withoutTenantScope()
                ->whereNull('tenant_id')
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $template->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => !empty($validated['is_default']),
            'config' => $config,
        ]);

        return redirect()->route('platform.certificates.templates.index')
            ->with('success', "Template \"{$template->name}\" updated successfully.");
    }

    public function preview(int $id)
    {
        $template = CertificateTemplate::withoutTenantScope()->findOrFail($id);
        $config = $template->getFullConfig();
        $branding = view()->shared('branding', []);

        // Build a sample certificate object for preview
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

    public function destroy(int $id)
    {
        $template = CertificateTemplate::withoutTenantScope()->findOrFail($id);

        if ($template->is_default) {
            return back()->with('error', 'Cannot delete the default template.');
        }

        $template->delete();

        return redirect()->route('platform.certificates.templates.index')
            ->with('success', "Template \"{$template->name}\" deleted.");
    }

    public function toggleActive(int $id)
    {
        $template = CertificateTemplate::withoutTenantScope()->findOrFail($id);
        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Template \"{$template->name}\" {$status}.");
    }

    private function cleanConfig(array $config): array
    {
        // Filter out empty signatures
        if (isset($config['signatures'])) {
            $config['signatures'] = array_values(array_filter($config['signatures'], function ($sig) {
                return !empty($sig['name']) || !empty($sig['title']);
            }));
        }

        // Cast booleans
        foreach (['show_score', 'show_logo', 'show_certificate_number', 'show_expiry', 'show_date_issued', 'show_course_duration', 'show_signatures'] as $boolKey) {
            if (isset($config[$boolKey])) {
                $config[$boolKey] = filter_var($config[$boolKey], FILTER_VALIDATE_BOOLEAN);
            }
        }

        return $config;
    }
}
