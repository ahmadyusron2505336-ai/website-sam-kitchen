@extends('layouts.merchant')
@section('title', 'Pesanan')

@section('content')
<div class="card max-w-2xl mx-auto space-y-3">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-extrabold">Detail Pesanan #{{ $order->code }}</h2>
        <span class="pill pill-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
    </div>

    <div class="text-sm space-y-1">
        <p><b>Pelanggan:</b> {{ $order->customer_name }} ({{ $order->customer_phone ?? '-' }})</p>
        <p><b>Alamat:</b> {{ $order->address ?? '-' }}</p>
        <p><b>Pembayaran:</b> {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '-' }}</p>
        <p><b>Waktu:</b> {{ $order->created_at->format('d M Y H:i') }}</p>
        <p><b>Penanggung jawab:</b> {{ $order->handler?->name ?? '-' }}</p>
        @if($order->note)<p><b>Catatan pelanggan:</b> {{ $order->note }}</p>@endif
        @if($order->reject_reason)<p class="text-red-600"><b>Alasan ditolak:</b> {{ $order->reject_reason }}</p>@endif
    </div>

       <div class="text-sm">
        <p class="font-bold">Alamat Pengantaran</p>
        <p class="text-cocoa/80">{{ $order->full_address }}</p>
    </div>

    @if($order->destination)
        <div class="rounded-xl overflow-hidden border border-orange-100">
            <iframe src="{{ maps_embed($order->destination) }}" title="Peta lokasi pengantaran" loading="lazy" allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade" class="w-full h-64 border-0"></iframe>
        </div>
        <p class="text-[11px] text-cocoa/60">
            Titik dicari otomatis dari alamat. Jika pin kurang pas, buka di Google Maps lalu sesuaikan.
        </p>
        <div class="flex flex-wrap gap-2">
            <a href="{{ maps_search($order->destination) }}" target="_blank" rel="noopener" class="btn btn-ghost">
                <i class="fa-solid fa-map-location-dot"></i> Buka di Google Maps
            </a>
            <a href="{{ maps_route($order->destination, auth()->user()->merchant?->address) }}" target="_blank" rel="noopener" class="btn btn-brand">
                <i class="fa-solid fa-route"></i> Rute dari Toko
            </a>
        </div>
    @endif    

    <table class="rtable">
        <thead><tr><th>Item</th><th>Qty</th><th>Harga</th><th class="text-right">Subtotal</th></tr></thead>
        <tbody>
        @foreach($order->items as $it)
            <tr>
                <td data-label="Item">{{ $it->product_name }}
                    @if($it->variant)<small class="text-cocoa/60">({{ $it->variant }})</small>@endif
                    @if($it->addons)<small class="text-cocoa/60">+ {{ $it->addons }}</small>@endif
                </td>
                <td data-label="Qty">{{ $it->qty }}</td>
                <td data-label="Harga">{{ rupiah($it->price) }}</td>
                <td data-label="Subtotal" class="text-right font-semibold">{{ rupiah($it->subtotal) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p class="text-right text-lg font-extrabold text-brand">Total: {{ rupiah($order->total) }}</p>
    <a href="{{ route('merchant.orders.index') }}" class="text-xs font-bold text-brand">← Kembali ke Pesanan</a>
</div>
@endsection