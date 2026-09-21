<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PolicyPushed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PolicyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('platform-admin')) {
            // Platform admin sees all policies grouped by tenant
            $tenants = Tenant::orderBy('name')->get();

            $query = Policy::withoutTenantScope()
                ->withCount('acknowledgments')
                ->with('tenant');

            if ($request->tenant_id) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->search) {
                $query->where('title', 'like', "%{$request->search}%");
            }

            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }

            $policies = $query->latest()->paginate(20)->withQueryString();

            return view('client.policies.index', compact('policies', 'tenants'));
        }

        // Client admin / manager sees only their tenant's policies
        $policies = Policy::withCount('acknowledgments')
            ->when($request->search, fn ($q, $s) => $q->where('title', 'like', "%{$s}%"))
            ->when($request->status === 'published', fn ($q) => $q->where('is_published', true))
            ->when($request->status === 'draft', fn ($q) => $q->where('is_published', false))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('client.policies.index', compact('policies'));
    }

    public function create()
    {
        $user = Auth::user();
        $tenants = null;

        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();
        }

        return view('client.policies.create', compact('tenants'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['nullable', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        $user = Auth::user();

        // Platform admin can assign to any tenant
        if ($user->hasRole('platform-admin') && !empty($validated['tenant_id'])) {
            $validated['tenant_id'] = $validated['tenant_id'];
        } else {
            $validated['tenant_id'] = $user->tenant_id;
        }

        $validated['created_by'] = Auth::id();
        $validated['version'] = $validated['version'] ?? '1.0';

        Policy::withoutTenantScope()->create($validated);

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy created successfully.');
    }

    public function show(Policy $policy)
    {
        // Platform admin can view any policy
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->with('acknowledgments.user', 'tenant')->findOrFail($policy->id);
        } else {
            $policy->load('acknowledgments.user');
        }

        return view('client.policies.show', compact('policy'));
    }

    public function edit(Policy $policy)
    {
        $tenants = null;

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
            $tenants = Tenant::orderBy('name')->get();
        }

        return view('client.policies.edit', compact('policy', 'tenants'));
    }

    public function update(Request $request, Policy $policy)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'version' => ['required', 'string', 'max:50'],
            'requires_acknowledgment' => ['boolean'],
            'acknowledgment_deadline_days' => ['nullable', 'integer', 'min:1'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->update($validated);

        return redirect()->route('manage.policies.show', $policy)
            ->with('success', 'Policy updated successfully.');
    }

    public function destroy(Policy $policy)
    {
        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->delete();

        return redirect()->route('manage.policies.index')
            ->with('success', 'Policy deleted successfully.');
    }

    /**
     * Import a PDF or Word document and extract its content for policy creation.
     */
    public function importDocument(Request $request)
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $file = $request->file('document');
        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';
        $title = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        try {
            if ($extension === 'pdf') {
                $content = $this->extractPdfText($file->getRealPath());
            } elseif (in_array($extension, ['docx'])) {
                $content = $this->extractDocxContent($file->getRealPath());
            } elseif ($extension === 'doc') {
                // .doc (legacy) — basic text extraction
                $raw = file_get_contents($file->getRealPath());
                // Strip binary, keep printable text
                $content = preg_replace('/[^\x20-\x7E\x0A\x0D\t]/', '', $raw);
                $content = trim(preg_replace('/\s{3,}/', "\n\n", $content));
            }
        } catch (\Exception $e) {
            Log::error('Policy document import failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to extract text from the uploaded document. Please try copying the content manually.');
        }

        if (empty(trim($content))) {
            return back()->with('error', 'Could not extract readable text from the document. The file may be scanned or image-based. Please copy and paste the content manually.');
        }

        // Convert plain text to basic HTML paragraphs
        $htmlContent = $this->textToHtml($content);

        $user = Auth::user();
        $tenants = null;
        if ($user->hasRole('platform-admin')) {
            $tenants = Tenant::orderBy('name')->get();
        }

        // Return to the create view with pre-filled content
        return view('client.policies.create', [
            'tenants' => $tenants,
            'importedTitle' => $title,
            'importedContent' => $htmlContent,
        ]);
    }

    /**
     * Extract text from a PDF file using pdftotext (poppler-utils).
     */
    private function extractPdfText(string $path): string
    {
        // Try pdftotext command first (most reliable on shared hosting)
        $output = [];
        $returnCode = 0;
        $escapedPath = escapeshellarg($path);

        exec("pdftotext {$escapedPath} - 2>/dev/null", $output, $returnCode);

        if ($returnCode === 0 && !empty($output)) {
            return implode("\n", $output);
        }

        // Fallback: basic PHP-based text extraction from PDF stream
        $raw = file_get_contents($path);

        // Extract text between BT and ET markers (basic PDF text extraction)
        preg_match_all('/BT\s*(.*?)\s*ET/s', $raw, $matches);
        $text = '';
        foreach ($matches[1] as $block) {
            // Extract text in parentheses (Tj/TJ operators)
            preg_match_all('/\(([^)]*)\)/', $block, $textMatches);
            $text .= implode(' ', $textMatches[1]) . "\n";
        }

        // Also try streams
        if (empty(trim($text))) {
            preg_match_all('/stream\s*(.*?)\s*endstream/s', $raw, $streams);
            foreach ($streams[1] as $stream) {
                $decoded = @gzuncompress($stream);
                if ($decoded) {
                    preg_match_all('/\(([^)]*)\)/', $decoded, $textMatches);
                    $text .= implode(' ', $textMatches[1]) . "\n";
                }
            }
        }

        return trim($text);
    }

    /**
     * Extract content from a DOCX file (Office Open XML).
     */
    private function extractDocxContent(string $path): string
    {
        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Could not open DOCX file.');
        }

        $content = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($content === false) {
            throw new \RuntimeException('Could not read document.xml from DOCX.');
        }

        // Parse XML and extract text with basic structure
        $xml = new \DOMDocument();
        $xml->loadXML($content, LIBXML_NOERROR | LIBXML_NOWARNING);

        $paragraphs = [];
        $pNodes = $xml->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'p');

        foreach ($pNodes as $p) {
            $text = '';
            $isBold = false;
            $isHeading = false;

            // Check for heading style
            $pPr = $p->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'pStyle');
            foreach ($pPr as $style) {
                $val = $style->getAttribute('w:val');
                if (preg_match('/Heading|Title/i', $val)) {
                    $isHeading = true;
                }
            }

            // Extract runs
            $runs = $p->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 'r');
            foreach ($runs as $r) {
                $tNodes = $r->getElementsByTagNameNS('http://schemas.openxmlformats.org/wordprocessingml/2006/main', 't');
                foreach ($tNodes as $t) {
                    $text .= $t->textContent;
                }
            }

            $text = trim($text);
            if (!empty($text)) {
                if ($isHeading) {
                    $paragraphs[] = '<h3>' . htmlspecialchars($text) . '</h3>';
                } else {
                    $paragraphs[] = '<p>' . htmlspecialchars($text) . '</p>';
                }
            }
        }

        return implode("\n", $paragraphs);
    }

    /**
     * Convert plain text to HTML paragraphs.
     */
    private function textToHtml(string $text): string
    {
        // If it already contains HTML tags, return as-is
        if (preg_match('/<[a-z][\s\S]*>/i', $text)) {
            return $text;
        }

        $lines = preg_split('/\n{2,}/', trim($text));
        $html = '';

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Detect headings (ALL CAPS lines or lines ending with colon)
            if (preg_match('/^[A-Z][A-Z\s\d.:,\-]{5,}$/', $line)) {
                $html .= '<h3>' . htmlspecialchars(mb_convert_case($line, MB_CASE_TITLE)) . '</h3>' . "\n";
            } else {
                $html .= '<p>' . nl2br(htmlspecialchars($line)) . '</p>' . "\n";
            }
        }

        return $html;
    }

    public function push(Request $request, Policy $policy)
    {
        $request->validate([
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'push_to_all' => ['boolean'],
        ]);

        if (Auth::user()->hasRole('platform-admin')) {
            $policy = Policy::withoutTenantScope()->findOrFail($policy->id);
        }

        $policy->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Send notifications to affected users
        $tenantId = $policy->tenant_id;
        if ($request->boolean('push_to_all')) {
            $users = User::where('tenant_id', $tenantId)->get();
        } elseif ($request->filled('user_ids')) {
            $users = User::whereIn('id', $request->user_ids)
                ->where('tenant_id', $tenantId)
                ->get();
        } else {
            $users = User::where('tenant_id', $tenantId)->get();
        }

        foreach ($users as $user) {
            $user->notify(new PolicyPushed($policy));
        }

        return back()->with('success', "Policy pushed to {$users->count()} user(s) successfully.");
    }
}
