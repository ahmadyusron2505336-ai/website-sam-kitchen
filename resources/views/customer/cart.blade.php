@extends('layouts.customer')
@section('title', 'Keranjang')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold mb-5">Keranjang Belanja</h1>

    @if(! $lines)
        <div class="text-center py-16 bg-white rounded-2xl border border-orange-100">
            <p class="text-5xl">🛒</p>
            <p class="mt-3 font-bold">Keranjang masih kosong</p>
            <a href="{{ route('customer.menu') }}" class="btn btn-brand mt-4">Lihat Menu</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($lines as $l)
                @php $p = $l['product']; @endphp
                <div class="bg-white rounded-2xl border border-orange-100 p-4 flex gap-4">
                    <div class="w-20 h-20 shrink-0 rounded-xl bg-brand-soft flex items-center justify-center overflow-hidden">
                        @if($p->image)
                            <img src="{{ asset('storage/'.$p->image) }}" alt="" class="w-full h-full object-cover">
                        @else
                            <span class="text-3xl">{{ category_icon($p->category?->name) }}</span>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <a href="{{ route('customer.menu.show', $p) }}" class="font-bold hover:underline">{{ $p->name }}</a>
                        <p class="text-xs text-cocoa/60">
                            {{ $l['variant']?->name ?? 'Standar' }}
                            @if($l['addons']->count()) · + {{ $l['addons']->pluck('name')->implode(', ') }} @endif
                        </p>
                        <p class="text-sm font-bold text-brand mt-1">{{ rupiah($l['unit']) }}</p>

                        <div class="flex flex-wrap items-center gap-3 mt-3">
                            <form method="POST" action="{{ route('customer.cart.update', $l['key']) }}">
                                @csrf @method('PATCH')
                                <input type="number" name="qty" value="{{ $l['qty'] }}" min="1" max="99"
                                       onchange="this.form.submit()" class="input !w-20 !py-1.5">
                            </form>
                            <form method="POST" action="{{ route('customer.cart.destroy', $l['key']) }}">
                                @csrf @method('DELETE')
                                <button class="text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </div>
                    </div>

                    <p class="font-extrabold whitespace-nowrap">{{ rupiah($l['subtotal']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-5 bg-white rounded-2xl border border-orange-100 p-5 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="mt-0.5 text-xs text-cocoa/60">
                    @if($l['variant'])
                        <span class="inline-block px-2 py-0.5 rounded-full bg-brand-soft text-brand font-bold">{{ $l['variant']->name }}</span>
                    @endif
                    @if($l['addons']->count()) + {{ $l['addons']->pluck('name')->implode(', ') }} @endif
                </p>
                <p class="text-2xl font-extrabold text-brand">{{ rupiah($total) }}</p>
            </div>
            <a href="{{ route('customer.checkout') }}" class="btn btn-brand !px-6 !py-3 !text-sm">Lanjut ke Checkout →</a>
        </div>
    @endif
</div>
@endsection