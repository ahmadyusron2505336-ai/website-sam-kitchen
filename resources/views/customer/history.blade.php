@extends('layouts.customer')
@section('title', 'Riwayat Pesanan')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
        <div>
            <h1 class="text-2xl font-extrabold">Riwayat Pesanan</h1>
            <p class="text-sm text-cocoa/60">Pantau status pesanan Anda. Riwayat disimpan di browser ini dan diperbarui otomatis.</p>
        </div>

        @if($orders->isNotEmpty())
            <form method="POST" action="{{ route('customer.history.clear') }}"
                  onsubmit="return confirm('Hapus semua riwayat di browser ini? Pesanan tetap tercatat di penjual.')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost !text-red-600"><i class="fa-solid fa-trash"></i> Hapus Semua Riwayat</button>
            </form>
        @endif
    </div>

    @if(session('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @forelse($orders as $order)
        @php
            // Ada produk yang belum diberi ulasan pada pesanan selesai?
            $unreviewed = $order->status === 'selesai' && $order->items->contains(
                fn ($i) => $i->product_id && ! $order->reviews->contains('product_id', $i->product_id)
            );
        @endphp

        <div class="bg-white rounded-2xl border border-orange-100 p-5 mb-3">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="font-extrabold">#{{ $order->code }}</p>
                    <p class="text-[11px] text-cocoa/50">
                        {{ $order->created_at->format('d M Y H:i') }}
                        · {{ \App\Models\Order::PAYMENT_METHODS[$order->payment_method] ?? '-' }}
                    </p>
                </div>
                <span class="pill pill-{{ $order->status }}">{{ \App\Models\Order::STATUS_LABELS[$order->status] ?? $order->status }}</span>
            </div>

            <p class="mt-2 text-xs text-cocoa/70">{{ \App\Models\Order::STATUS_HINTS[$order->status] ?? '' }}</p>
            @if($order->status === 'ditolak' && $order->reject_reason)
                <p class="mt-1 text-xs text-red-600"><b>Alasan:</b> {{ $order->reject_reason }}</p>
            @endif

            <p class="mt-2 text-sm text-cocoa/80 line-clamp-2">{{ $order->items_summary }}</p>

            <div class="flex flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-orange-100">
                <span class="font-extrabold text-brand">{{ rupiah($order->total) }}</span>

                <div class="flex flex-wrap gap-2">
                    @if($unreviewed)
                        <a href="{{ route('customer.order', $order->token) }}#ulasan" class="btn btn-brand !py-2 !text-xs">
                            <i class="fa-solid fa-star"></i> Beri Rating &amp; Ulasan
                        </a>
                    @endif

                    <a href="{{ route('customer.order', $order->token) }}" class="btn btn-ghost !py-2 !text-xs">Lihat Detail</a>

                    @if($order->can_be_cancelled)
                        <form method="POST" action="{{ route('customer.order.cancel', $order->token) }}"
                              onsubmit="return confirm('Batalkan pesanan #{{ $order->code }}? Pesanan yang dibatalkan tidak bisa diaktifkan lagi.')">
                            @csrf
                            <button class="btn btn-ghost !py-2 !text-xs !text-red-600">
                                <i class="fa-solid fa-ban"></i> Batalkan Pesanan
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('customer.history.forget', $order->token) }}"
                          onsubmit="return confirm('Hapus pesanan #{{ $order->code }} dari riwayat? Pesanan tetap tercatat di penjual.')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost !py-2 !text-xs !text-red-600" title="Hapus dari riwayat">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-sand rounded-2xl">
            <p class="text-5xl">🧾</p>
            <p class="mt-3 font-bold">Belum ada pesanan di browser ini</p>
            <p class="text-xs text-cocoa/60 mt-1">Pesanan yang Anda buat akan muncul di sini.</p>
            <a href="{{ route('customer.menu') }}" class="btn btn-brand mt-4">Mulai Pesan</a>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
// Muat ulang otomatis jika ada status pesanan yang berubah
const known = @json($orders->pluck('status', 'token'));
setInterval(async () => {
    try {
        const r = await fetch('{{ route('customer.history.status') }}', { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (Object.keys(d).some(t => known[t] !== d[t])) location.reload();
    } catch (e) {}
}, 10000);
</script>
@endpush