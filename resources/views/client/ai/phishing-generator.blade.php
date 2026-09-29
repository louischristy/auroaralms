@extends('layouts.app')
@section('title', 'AI Phishing Template Generator')

@section('content')
<div class="max-w-5xl mx-auto py-8 px-4 sm:px-6" x-data="aiPhishingGenerator" data-generate-url="{{ route('manage.ai.phishing-generate') }}">
    <div class="mb-6">
        <a href="{{ route('manage.ai.index') }}" class="text-sm text-amber-600 hover:text-amber-800">&larr; Back to AI Tools</a>
        <h1 class="text-2xl font-bold text-gray-900 mt-2">AI Phishing Template Generator</h1>
        <p class="mt-1 text-sm text-gray-500">Generate realistic phishing email templates for security awareness training simulations.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
        <form @submit.prevent="generate">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scenario Type</label>
                    <select x-model="form.scenario_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="credential_harvest">Credential Harvest</option>
                        <option value="business_email_compromise">Business Email Compromise</option>
                        <option value="invoice_fraud">Invoice Fraud</option>
                        <option value="malware_download">Malware Download</option>
                        <option value="data_exfiltration">Data Exfiltration</option>
                        <option value="tech_support_scam">Tech Support Scam</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Difficulty</label>
                    <select x-model="form.difficulty" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="easy">Easy (obvious red flags)</option>
                        <option value="medium">Medium (subtle red flags)</option>
                        <option value="hard">Hard (very convincing)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Industry</label>
                    <input type="text" x-model="form.industry" placeholder="e.g., Healthcare, Finance, Technology" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Templates</label>
                    <select x-model="form.count" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        <option value="1">1 Template</option>
                        <option value="2">2 Templates</option>
                        <option value="3" selected>3 Templates</option>
                        <option value="5">5 Templates</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-3">
                <button type="submit" :disabled="loading" class="inline-flex items-center px-4 py-2 bg-amber-600 text-white rounded-lg font-medium text-sm hover:bg-amber-700 disabled:opacity-50 disabled:cursor-wait">
                    <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    <span x-text="loading ? 'Generating...' : 'Generate Templates'">Generate Templates</span>
                </button>
                <span x-show="error" class="text-sm text-red-600" x-text="error"></span>
            </div>
        </form>
    </div>

    <template x-if="result && result.templates">
        <div class="space-y-6">
            <template x-for="(tpl, idx) in result.templates" :key="idx">
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="p-5 border-b border-gray-100">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-lg font-semibold text-gray-900" x-text="tpl.name"></h3>
                            <span :class="{
                                'bg-green-100 text-green-800': tpl.difficulty === 'easy',
                                'bg-yellow-100 text-yellow-800': tpl.difficulty === 'medium',
                                'bg-red-100 text-red-800': tpl.difficulty === 'hard'
                            }" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" x-text="tpl.difficulty"></span>
                        </div>
                        <div class="text-sm text-gray-500 space-y-1">
                            <p><strong>Subject:</strong> <span x-text="tpl.subject"></span></p>
                            <p><strong>From:</strong> <span x-text="tpl.sender_name"></span> &lt;<span x-text="tpl.sender_email"></span>&gt;</p>
                        </div>
                    </div>
                    <div class="p-5 bg-gray-50">
                        <p class="text-xs font-semibold text-gray-400 uppercase mb-2">Email Body Preview</p>
                        <div class="bg-white rounded-lg border border-gray-200 p-4 text-sm" x-html="tpl.body_html"></div>
                    </div>
                    <div x-show="tpl.red_flags && tpl.red_flags.length" class="p-5 border-t border-gray-100">
                        <p class="text-xs font-semibold text-red-500 uppercase mb-2">Red Flags to Identify</p>
                        <ul class="space-y-1">
                            <template x-for="flag in tpl.red_flags" :key="flag">
                                <li class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10l-4 6 4 6H6a3 3 0 01-3-3V6z" clip-rule="evenodd"/></svg>
                                    <span x-text="flag"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </template>

            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('manage.ai.phishing-save') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 text-white rounded-lg font-medium text-sm hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save All Templates
                    </button>
                </form>
                <button @click="result = null" class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Discard & Regenerate</button>
            </div>
        </div>
    </template>
</div>

@endsection
