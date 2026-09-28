@extends('layouts.app')
@section('title', 'Edit Subscription — ' . ($branding['platform_name'] ?? 'Auroara LMS'))

@section('content')
<div class="max-w-6xl space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <a href="{{ route('platform.subscriptions.index') }}" class="text-sm text-gray-500 hover:text-primary">&larr; Subscriptions</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">Edit Subscription: {{ $subscription->tenant->name }}</h1>
        </div>
        <form method="POST" action="{{ route('platform.subscriptions.generate-invoice', $subscription->id) }}">
            @csrf
            <button class="btn-outline">Generate Invoice</button>
        </form>
    </div>

    <form method="POST" action="{{ route('platform.subscriptions.update', $subscription->id) }}">
        @method('PUT')
        @include('platform.subscriptions._form', ['submitLabel' => 'Save Changes'])
    </form>

    @if($subscription->invoices->isNotEmpty())
        <div class="card">
            <div class="px-6 py-4 border-b border-gray-100"><h2 class="text-base font-semibold text-gray-900">Invoices</h2></div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach($subscription->invoices->sortByDesc('issued_at') as $inv)
                        <tr>
                            <td class="px-6 py-3 font-medium text-gray-900">{{ $inv->invoice_number }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $inv->issued_at->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-right">{{ $inv->currency }} {{ number_format($inv->total, 2) }}</td>
                            <td class="px-6 py-3 text-center"><span class="{{ $inv->isPaid() ? 'badge-success' : ($inv->isOverdue() ? 'badge-danger' : 'badge-warning') }}">{{ ucfirst($inv->isOverdue() ? 'overdue' : $inv->status) }}</span></td>
                            <td class="px-6 py-3 text-right"><a href="{{ route('platform.subscriptions.invoice', $inv->id) }}" class="text-secondary hover:text-primary text-xs font-medium">View</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
