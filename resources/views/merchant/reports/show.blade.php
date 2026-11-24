@extends('layouts.merchant')
@section('title', 'Laporan')

@section('content')
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-extrabold">Rincian Transaksi</h2>
            <p class="text-[11px] text-cocoa/60">{{ $day->copy()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
        </div>
        <a href="{{ route('merchant.reports.index') }}" class="text-xs font-bold text-brand no-print">← Kembali ke Laporan</a>
    </div>

    <table class="rtable">
        <thead>
            <tr><th>Kode</th><th>Jam</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Admin</th></tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr>
                <td data-label="Kode" class="font-bold">
                    <a href="{{ route('merchant.orders.show', $o) }}" class="hover:underline">#{{ $o->code }}</a>
                </td>
                <td data-label="Jam">{{ $o->created_at->format('H:i') }}</td>
                <td data-label="Pelanggan">{{ $o->customer_name }}</td>
                <td data-label="Item" class="md:max-w-xs">{{ $o->items_summary }}</td>
                <td data-label="Total" class="font-bold text-brand">{{ rupiah($o->total) }}</td>
                <td data-label="Admin">{{ $o->handler?->name ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-cocoa/50 py-6">Tidak ada transaksi.</td></tr>
        @endforelse
        </tbody>
    </table>

    <p class="text-right mt-4 font-extrabold">Total hari ini: <span class="text-brand">{{ rupiah($orders->sum('total')) }}</span></p>
</div>
@endsection