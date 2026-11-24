@extends('layouts.merchant')
@section('title', 'Laporan')

@section('content')
<div class="card">
    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-3 mb-5">
        <div>
            <h2 class="text-lg font-extrabold">Laporan Penjualan &amp; Keuangan</h2>
            <p class="text-[11px] text-cocoa/60">Ringkasan transaksi dan evaluasi performa toko</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 no-print">
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" onchange="this.form.submit()" class="input !w-auto !py-1.5">
                <span class="text-xs font-bold">s/d</span>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" onchange="this.form.submit()" class="input !w-auto !py-1.5">
            </form>
            <button type="button" onclick="window.print()" class="btn btn-red">Unduh PDF</button>
            <a href="{{ route('merchant.reports.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
               class="btn btn-green">Ekspor Excel</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-orange-200 bg-orange-50 p-4">
            <p class="text-[11px] text-cocoa/70">Total Pendapatan (Periode Ini)</p>
            <p class="text-2xl font-extrabold text-brand">{{ rupiah($revenue) }}</p>
            @if($growth !== null)
                <p class="text-[11px] font-bold {{ $growth >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    <i class="fa-solid {{ $growth >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                    {{ $growth >= 0 ? '+' : '' }}{{ $growth }}% dari periode sebelumnya
                </p>
            @else
                <p class="text-[11px] text-cocoa/50">Belum ada pembanding</p>
            @endif
        </div>
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
            <p class="text-[11px] text-cocoa/70">Total Transaksi Selesai</p>
            <p class="text-2xl font-extrabold">{{ number_format($count, 0, ',', '.') }} Pesanan</p>
            <p class="text-[11px] text-cocoa/60">Rata-rata {{ $perDay }} pesanan/hari</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-[11px] text-cocoa/70">Menu Paling Terjual</p>
            <p class="text-xl font-extrabold">{{ $top->product_name ?? '-' }}</p>
            <p class="text-[11px] font-bold text-red-600">{{ $top ? $top->sold.' Terjual' : '' }}</p>
        </div>
    </div>

    <h3 class="font-bold text-sm mb-2">Rekapitulasi Penjualan Harian</h3>
    <table class="rtable">
        <thead>
            <tr>
                <th>Tanggal</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th>
                <th>Admin Penanggung Jawab</th><th class="text-center no-print">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($daily as $r)
            <tr>
                <td data-label="Tanggal">{{ \Carbon\Carbon::parse($r->tanggal)->locale('id')->isoFormat('DD MMM YYYY') }}</td>
                <td data-label="Transaksi">{{ $r->jumlah }} Transaksi</td>
                <td data-label="Pendapatan" class="font-bold">{{ rupiah($r->pendapatan) }}</td>
                <td data-label="Admin">{{ $r->admin ?: '-' }}</td>
                <td data-label="Aksi" class="text-center no-print">
                    <a href="{{ route('merchant.reports.show', $r->tanggal) }}"
                       class="text-xs font-bold text-brand hover:underline">Detail</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center text-cocoa/50 py-6">Tidak ada transaksi pada periode ini.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection