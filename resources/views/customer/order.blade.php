@extends('layouts.customer')
@section('title', 'Pesanan #'.$order->code)

@section('content')
@php
    $store  = $order->merchant;
    $method = $order->payment_method;
@endphp

<div class="max-w-3xl mx-auto px-4 py-8 space-y-4">

    {{-- Status --}}
    <div class="bg-white rounded-2xl border border-orange-100 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <p class="text-xs text-cocoa/50">Nomor Pesanan</p>
                <p class="text-xl font-extrabold">#{{ $order->code }}</p>
                <p class="text-[11px] text-cocoa/50">{{ $order->created_at->format('d M Y H:i') }}</p>
            </div>
            <span class="pill pill-{{ $order->status }}">{{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}</span>
        </div>

        <p class="mt-3 text-sm text-cocoa/80">{{ \App\Models\Order::STATUS_HINTS[$order->status] ?? '' }}</p>

        @if($order->status === 'ditolak')
            @if($order->reject_reason)
                <p class="mt-2 text-xs text-red-600"><b>Alasan:</b> {{ $order->reject_reason }}</p>
            @endif
        @else
            @php $step = ['menunggu' => 1, 'diproses' => 2, 'selesai' => 3][$order->status] ?? 1; @endphp
            <ol class="mt-4 flex items-start">
                @foreach([[1, 'Pesanan Dikirim'], [2, 'Diterima & Diproses'], [3, 'Selesai']] as [$n, $label])
                    <li class="flex-1 text-center relative">
                        @if($n > 1)
                            <span class="absolute top-4 -left-1/2 w-full h-0.5 {{ $step >= $n ? 'bg-brand' : 'bg-gray-200' }}"></span>
                        @endif
                        <span class="relative z-10 mx-auto w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $step >= $n ? 'bg-brand text-white' : 'bg-gray-200 text-gray-500' }}">
                            @if($step >= $n)<i class="fa-solid fa-check"></i>@else{{ $n }}@endif
                        </span>
                        <p class="mt-1 text-[11px] font-semibold {{ $step >= $n ? 'text-cocoa' : 'text-cocoa/40' }}">{{ $label }}</p>
                    </li>
                @endforeach
            </ol>
        @endif

        <div class="mt-3 flex items-center gap-2 p-3 rounded-xl bg-sand text-xs">
            <i class="fa-solid fa-link text-brand"></i>
            <span class="flex-1">Simpan tautan halaman ini untuk memantau status pesanan dan memberi ulasan.</span>
        </div>
    </div>

    {{-- Pembayaran --}}
    @if($order->status !== 'ditolak' && $method)
        <div class="bg-white rounded-2xl border border-orange-100 p-5">
            <h2 class="font-bold mb-3">Pembayaran: {{ \App\Models\Order::PAYMENT_METHODS[$method] ?? '-' }}</h2>

            @if($method === 'transfer')
                <div class="rounded-xl bg-sand p-4 text-sm space-y-1">
                    <p><span class="text-cocoa/60">Bank:</span> <b>{{ $store->bank_name ?: '-' }}</b></p>
                    <p><span class="text-cocoa/60">No. Rekening:</span> <b class="select-all">{{ $store->bank_account_number }}</b></p>
                    <p><span class="text-cocoa/60">Atas Nama:</span> <b>{{ $store->bank_account_name ?: '-' }}</b></p>
                    <p><span class="text-cocoa/60">Jumlah Transfer:</span> <b class="text-brand">{{ rupiah($order->total) }}</b></p>
                </div>
            @elseif($method === 'qris')
                @if($store->qris_image)
                    <div class="text-center">
                        <img src="{{ asset('storage/'.$store->qris_image) }}" alt="QRIS SAM Kitchen"
                             class="mx-auto w-56 h-56 object-contain rounded-xl border border-orange-100 bg-white">
                        <p class="mt-2 text-sm">Scan lalu bayar sebesar <b class="text-brand">{{ rupiah($order->total) }}</b></p>
                    </div>
                @else
                    <p class="text-sm text-cocoa/60">Kode QRIS belum tersedia. Silakan hubungi penjual via WhatsApp.</p>
                @endif
            @else
                <p class="text-sm">Siapkan uang tunai sebesar <b class="text-brand">{{ rupiah($order->total) }}</b> dan bayar saat pesanan diterima.</p>
            @endif

            @if(in_array($method, ['transfer', 'qris']))
                <a href="{{ wa_link('Halo SAM Kitchen, saya sudah membayar pesanan #'.$order->code.' sebesar '.rupiah($order->total).' via '.(\App\Models\Order::PAYMENT_METHODS[$method] ?? '').'. Berikut bukti pembayarannya.') }}"
                   target="_blank" rel="noopener" class="btn !bg-[#25D366] mt-4">
                    <i class="fa-brands fa-whatsapp"></i> Kirim Bukti Pembayaran
                </a>
            @endif
        </div>
    @endif

    {{-- Data pengantaran --}}
    <div class="bg-white rounded-2xl border border-orange-100 p-5 text-sm space-y-1">
        <h2 class="font-bold mb-2">Pengantaran</h2>
        <p><span class="text-cocoa/60">Nama:</span> <b>{{ $order->customer_name }}</b></p>
        <p><span class="text-cocoa/60">Alamat:</span> {{ $order->address ?? '-' }}</p>
        @if($order->note)<p><span class="text-cocoa/60">Catatan:</span> {{ $order->note }}</p>@endif
    </div>

    {{-- Item + ulasan --}}
    <div class="bg-white rounded-2xl border border-orange-100 p-5">
        <h2 id="ulasan" class="font-bold mb-1">Rincian Pesanan</h2>
        <ul class="divide-y divide-orange-100">
            @foreach($order->items as $item)
                @php $rv = $order->reviews->firstWhere('product_id', $item->product_id); @endphp
                <li class="py-3">
                    <div class="flex justify-between gap-3 text-sm">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $item->qty }}× {{ $item->product_name }}</p>
                            <p class="text-[11px] text-cocoa/60">
                                {{ $item->variant ?? 'Standar' }}@if($item->addons) · + {{ $item->addons }}@endif
                            </p>
                        </div>
                        <span class="font-bold whitespace-nowrap">{{ rupiah($item->subtotal) }}</span>
                    </div>

                    @if($rv)
                        <div class="mt-2 p-3 rounded-xl bg-sand text-xs">
                            <p class="font-bold mb-0.5">Ulasan Anda</p>
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rv->rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                            @endfor
                            @if($rv->comment)<p class="mt-1 text-cocoa/80">{{ $rv->comment }}</p>@endif
                        </div>
                    @elseif($order->status === 'selesai' && $item->product_id)
                        <form method="POST" action="{{ route('customer.reviews.store', $order->token) }}" class="mt-2 p-3 rounded-xl bg-sand space-y-2">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <p class="text-xs font-bold">Beri rating untuk {{ $item->product_name }}</p>
                            <div class="stars">
                                @for($s = 5; $s >= 1; $s--)
                                    <input type="radio" name="rating" id="r{{ $item->id }}-{{ $s }}" value="{{ $s }}">
                                    <label for="r{{ $item->id }}-{{ $s }}" title="{{ $s }} bintang">★</label>
                                @endfor
                            </div>
                            <textarea name="comment" rows="2" maxlength="500" placeholder="Tulis ulasan Anda (opsional)" class="input"></textarea>
                            <button class="btn btn-brand !py-2 !text-xs">Kirim Ulasan</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="flex justify-between items-center pt-3 border-t border-orange-100">
            <span class="text-sm font-bold">Total</span>
            <span class="text-lg font-extrabold text-brand">{{ rupiah($order->total) }}</span>
        </div>

        @if(! in_array($order->status, ['selesai', 'ditolak']))
            <p class="mt-2 text-[11px] text-cocoa/50">Form ulasan muncul setelah pesanan berstatus Selesai. Muat ulang halaman ini untuk melihat status terbaru.</p>
        @endif
    </div>

    <a href="{{ route('customer.menu') }}" class="inline-block text-xs font-bold text-brand">← Pesan menu lainnya</a>
</div>
@endsection

@push('scripts')
<script>
const currentStatus = @json($order->status);
setInterval(async () => {
    try {
        const r = await fetch('{{ route('customer.order.status', $order->token) }}', { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (d.status !== currentStatus) location.reload();
    } catch (e) {}
}, 10000);
</script>
@endpush