@extends('layouts.app')
@section('title', 'Invoice ' . $invoice->invoice_number . ' — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@php
    $sym = ['USD' => '$', 'MYR' => 'RM ', 'SGD' => 'S$', 'AED' => 'AED ', 'SAR' => 'SAR '][$invoice->currency] ?? $invoice->currency.' ';
    $fmt = fn ($n) => $sym . number_format((float) $n, 2);
    $status = $invoice->isOverdue() ? 'overdue' : $invoice->status;
    $statusCls = ['paid' => 'bg-green-100 text-green-700', 'pending' => 'bg-amber-100 text-amber-700', 'overdue' => 'bg-red-100 text-red-700'][$status] ?? 'bg-gray-100 text-gray-700';
    $platform = $branding['platform_name'] ?? 'Auroara LMS';
@endphp


@section('content')
<style>
    @media print {
        aside, header, nav, .no-print { display: none !important; }
        html, body, body div { height: auto !important; overflow: visible !important; }
        body { background: #fff !important; }
        main, .print-area { margin: 0 !important; padding: 0 !important; max-width: none !important; }
        .invoice-sheet { box-shadow: none !important; border: 0 !important; }
        @page { margin: 16mm; }
    }
</style>
<div class="max-w-3xl mx-auto space-y-4 print-area">
    <div class="no-print flex items-center justify-between">
        <a href="{{ route('platform.subscriptions.edit', $invoice->subscription_id) }}" class="text-sm text-gray-500 hover:text-primary">&larr; Back to subscription</a>
        <button type="button" x-data @click="window.print()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print / Save PDF
        </button>
    </div>

    <div class="invoice-sheet card p-8 sm:p-12 bg-white">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                @if(!empty($branding['logo_url']))
                    <img src="{{ $branding['logo_url'] }}" alt="{{ $platform }}" class="h-10 mb-2">
                @endif
                <p class="text-xl font-bold text-primary">{{ $platform }}</p>
                <p class="text-sm text-gray-500">Learning management platform</p>
            </div>
            <div class="text-right">
                <p class="text-3xl font-bold tracking-tight text-gray-900">INVOICE</p>
                <p class="mt-1 text-sm font-medium text-gray-700">{{ $invoice->invoice_number }}</p>
                <span class="mt-2 inline-block rounded-full px-3 py-0.5 text-xs font-semibold uppercase {{ $statusCls }}">{{ $status }}</span>
            </div>
        </div>

        <div class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-8 text-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Billed to</p>
                <p class="mt-1 font-semibold text-gray-900">{{ $invoice->tenant->name }}</p>
                @if($invoice->tenant->domain)<p class="text-gray-500">{{ $invoice->tenant->domain }}</p>@endif
            </div>
            <dl class="space-y-1 sm:text-right">
                <div><dt class="inline text-gray-500">Issued:</dt> <dd class="inline font-medium">{{ $invoice->issued_at->format('d M Y') }}</dd></div>
                <div><dt class="inline text-gray-500">Due:</dt> <dd class="inline font-medium">{{ $invoice->due_at->format('d M Y') }}</dd></div>
                @if($invoice->subscription?->plan)<div><dt class="inline text-gray-500">Plan:</dt> <dd class="inline font-medium">{{ $invoice->subscription->plan->name }}</dd></div>@endif
                @if($invoice->paid_at)<div><dt class="inline text-gray-500">Paid:</dt> <dd class="inline font-medium">{{ $invoice->paid_at->format('d M Y') }}</dd></div>@endif
            </dl>
        </div>

        <table class="mt-10 w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-900 text-left text-xs uppercase tracking-wider text-gray-500">
                    <th class="py-2 pr-4 font-semibold">Description</th>
                    <th class="py-2 px-4 text-right font-semibold">Qty</th>
                    <th class="py-2 px-4 text-right font-semibold">Unit price</th>
                    <th class="py-2 pl-4 text-right font-semibold">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($invoice->line_items ?? [] as $line)
                    <tr>
                        <td class="py-3 pr-4 text-gray-800">{{ $line['description'] }}</td>
                        <td class="py-3 px-4 text-right text-gray-600">{{ $line['quantity'] }}</td>
                        <td class="py-3 px-4 text-right text-gray-600">{{ $fmt($line['unit_price']) }}</td>
                        <td class="py-3 pl-4 text-right font-medium text-gray-900">{{ $fmt($line['amount']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 flex justify-end">
            <dl class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>{{ $fmt($invoice->subtotal) }}</dd></div>
                @if((float) $invoice->discount_amount > 0)
                    <div class="flex justify-between text-green-700"><dt>Discount</dt><dd>&minus;{{ $fmt($invoice->discount_amount) }}</dd></div>
                @endif
                @if((float) $invoice->tax_amount > 0)
                    <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd>{{ $fmt($invoice->tax_amount) }}</dd></div>
                @endif
                <div class="flex justify-between border-t-2 border-gray-900 pt-3 text-base font-bold text-gray-900"><dt>Total due</dt><dd>{{ $fmt($invoice->total) }} <span class="text-xs font-medium text-gray-500">{{ $invoice->currency }}</span></dd></div>
            </dl>
        </div>

        @if($invoice->notes)
            <div class="mt-10 rounded-lg bg-gray-50 p-4 text-sm text-gray-600"><span class="font-semibold text-gray-700">Notes:</span> {{ $invoice->notes }}</div>
        @endif

        <p class="mt-12 border-t border-gray-100 pt-4 text-center text-xs text-gray-400">Thank you for your business. Payment due within 30 days of the issue date.</p>
    </div>
</div>
@endsection
