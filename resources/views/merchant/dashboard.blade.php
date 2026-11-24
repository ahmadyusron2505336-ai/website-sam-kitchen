@extends('layouts.merchant')
@section('title', 'Dashboard')

@section('content')
@php
    $stats = [
        ['Total Penjualan',  rupiah($totalSales),                            'fa-chart-line'],
        ['Total Pesanan',    number_format($totalOrders, 0, ',', '.'),       'fa-clipboard-list'],
        ['Total Pelanggan',  number_format($totalCustomers, 0, ',', '.'),    'fa-users'],
    ];
@endphp

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    @foreach($stats as [$label, $value, $icon])
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 shrink-0 rounded-xl bg-brand-soft text-brand flex items-center justify-center text-lg">
                <i class="fa-solid {{ $icon }}"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-semibold tracking-wider uppercase text-cocoa/60">{{ $label }}</p>
                <p class="text-xl md:text-2xl font-extrabold truncate">{{ $value }}</p>
            </div>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4 min-w-0">

        <div class="card">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h3 class="font-bold">Grafik Penjualan</h3>
                <form method="GET">
                    <select name="range" onchange="this.form.submit()"
                            class="bg-cocoa text-white text-xs font-bold rounded-lg px-3 py-1.5">
                        <option value="7"  @selected($range === '7')>7 Hari Terakhir</option>
                        <option value="30" @selected($range === '30')>30 Hari Terakhir</option>
                        <option value="12" @selected($range === '12')>12 Bulan Terakhir</option>
                    </select>
                </form>
            </div>
            <div class="relative h-56 sm:h-72"><canvas id="salesChart"></canvas></div>
        </div>

        <div class="card">
            <h3 class="font-bold mb-4">Produk Terlaris</h3>
            @forelse($bestSellers as $b)
                <div class="mb-3">
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span>{{ $b->product_name }}</span><span>{{ $b->percent }}%</span>
                    </div>
                    <div class="h-2.5 rounded-full bg-gray-200 overflow-hidden">
                        <div class="h-full rounded-full bg-cocoa" style="width: {{ $b->percent }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-cocoa/50">Belum ada data penjualan.</p>
            @endforelse
        </div>
    </div>

    <div class="card">
        <h3 class="font-bold mb-2">Pesanan Terbaru</h3>
        <ol class="divide-y divide-orange-100">
            @forelse($latestOrders as $i => $o)
                <li class="flex items-center gap-3 py-3">
                    <span class="w-5 text-sm font-semibold">{{ $i + 1 }}.</span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate">{{ $o->customer_name }}</p>
                        <p class="text-[11px] text-cocoa/60 truncate">{{ $o->items_summary }}</p>
                    </div>
                    <a href="{{ route('merchant.orders.show', $o) }}" class="btn btn-brown">Lihat Detail</a>
                </li>
            @empty
                <li class="py-4 text-sm text-cocoa/50">Belum ada pesanan.</li>
            @endforelse
        </ol>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [{
            data: @json($values),
            borderColor: '#4A2C1A', borderWidth: 2.5, tension: .4, fill: true,
            backgroundColor: 'rgba(217,93,43,.15)',
            pointBackgroundColor: '#D95D2B', pointBorderColor: '#4A2C1A', pointRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => rp(c.parsed.y) } } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: true } },
            y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => v >= 1000 ? (v / 1000) + 'K' : v } }
        }
    }
});
</script>
@endpush