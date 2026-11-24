@extends('layouts.customer')
@section('title', $product->name)

@section('content')
@php
    $habis       = $product->stock <= 0 || ! $product->is_available;
    $hasVariants = $product->variants->isNotEmpty();

    // Baris jumlah: satu per varian, atau satu baris saja jika tanpa varian (kunci 0)
    $rows = $hasVariants
        ? $product->variants->map(fn ($v) => ['id' => $v->id, 'name' => $v->name, 'price' => $v->price, 'default' => 0])->all()
        : [['id' => 0, 'name' => null, 'price' => $product->price, 'default' => 1]];
@endphp

<div class="max-w-5xl mx-auto px-4 py-8">
    <a href="{{ route('customer.menu') }}" class="text-xs font-bold text-brand">← Kembali ke Menu</a>

    <div class="mt-4 grid md:grid-cols-2 gap-8">
        {{-- Foto --}}
        <div class="aspect-square rounded-3xl bg-brand-soft flex items-center justify-center overflow-hidden">
            @if($product->image)
                <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
            @else
                <span class="text-8xl">{{ category_icon($product->category?->name) }}</span>
            @endif
        </div>

        {{-- Detail & form pesan --}}
        <div>
            <p class="text-xs font-bold text-brand uppercase tracking-wide">{{ $product->category?->name ?? 'Menu' }}</p>
            <h1 class="text-2xl sm:text-3xl font-extrabold">{{ $product->name }}</h1>

            <div class="flex items-center gap-1.5 text-sm mt-1">
                <i class="fa-solid fa-star text-amber-400"></i>
                @if($product->reviews_count)
                    <b>{{ number_format($product->reviews_avg_rating, 1) }}</b>
                    <span class="text-cocoa/50">({{ $product->reviews_count }} ulasan)</span>
                @else
                    <span class="text-cocoa/50">Belum ada ulasan</span>
                @endif
            </div>

            <p class="mt-3 text-2xl font-extrabold text-brand">
                @if($hasVariants)<span class="text-sm font-semibold text-cocoa/60">Mulai </span>@endif{{ rupiah($product->price) }}
            </p>
            @if($product->description)<p class="mt-2 text-sm text-cocoa/70">{{ $product->description }}</p>@endif
            <p class="mt-2">
                <span class="badge {{ $habis ? 'badge-low' : 'badge-ok' }}">{{ $habis ? 'Stok habis' : 'Tersedia ('.$product->stock.' porsi)' }}</span>
            </p>

            <form method="POST" action="{{ route('customer.cart.store') }}" id="orderForm" class="mt-5 space-y-5">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                {{-- Varian isi / jumlah --}}
                <div>
                    <p class="lbl">{{ $hasVariants ? 'Pilih Isi / Varian' : 'Jumlah' }}</p>
                    @if($hasVariants)
                        <p class="text-[11px] text-cocoa/60 mb-2">Anda dapat memilih lebih dari satu varian sekaligus.</p>
                    @endif

                    <div class="space-y-2">
                        @foreach($rows as $r)
                            <div class="flex items-center justify-between gap-3 {{ $hasVariants ? 'p-3 rounded-xl border border-orange-100 bg-white' : '' }}">
                                <div class="min-w-0">
                                    @if($r['name'])<p class="text-sm font-bold">{{ $r['name'] }}</p>@endif
                                    <p class="text-xs font-bold text-brand">{{ rupiah($r['price']) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="step w-9 h-9 rounded-lg bg-sand font-bold text-lg"
                                            data-target="qty-{{ $r['id'] }}" data-step="-1">−</button>
                                    <input type="number" id="qty-{{ $r['id'] }}" name="qty[{{ $r['id'] }}]"
                                           value="{{ old('qty.'.$r['id'], $r['default']) }}" min="0" max="99"
                                           data-price="{{ $r['price'] }}" class="qty-input input !w-16 text-center">
                                    <button type="button" class="step w-9 h-9 rounded-lg bg-sand font-bold text-lg"
                                            data-target="qty-{{ $r['id'] }}" data-step="1">+</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Add-on --}}
                @if($product->addons->count())
                    <div>
                        <p class="lbl">Tambahan (Add-on)</p>
                        <p class="text-[11px] text-cocoa/60 mb-2">Berlaku untuk setiap porsi yang dipilih.</p>
                        <div class="space-y-2">
                            @foreach($product->addons as $a)
                                <label class="flex items-center justify-between gap-3 p-3 rounded-xl border border-orange-100 bg-white cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-brand-soft">
                                    <span class="flex items-center gap-2.5 text-sm font-semibold">
                                        <input type="checkbox" name="addons[]" value="{{ $a->id }}" data-addon="{{ $a->price }}" class="accent-[#D95D2B]">
                                        {{ $a->name }}
                                    </span>
                                    <span class="text-xs text-cocoa/60">+{{ rupiah($a->price) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3 pt-2 border-t border-orange-100">
                    <div>
                        <p class="text-xs text-cocoa/60">Total (<span id="totalCount">0 porsi</span>)</p>
                        <p id="totalPrice" class="text-xl font-extrabold">Rp 0</p>
                    </div>
                    <button id="submitBtn" class="btn btn-brand !px-6 !py-3 !text-sm"
                            @if($habis) data-locked="1" disabled @endif>
                        <i class="fa-solid fa-cart-plus"></i> {{ $habis ? 'Stok Habis' : 'Tambah ke Keranjang' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ulasan --}}
    <section class="mt-12">
        <h2 class="text-xl font-extrabold mb-4">Ulasan Pelanggan</h2>
        <div class="space-y-3">
            @forelse($reviews as $r)
                <div class="bg-white rounded-2xl border border-orange-100 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-bold text-sm">{{ $r->reviewer_name }}</p>
                        <p class="text-[11px] text-cocoa/50">{{ $r->created_at->format('d M Y') }}</p>
                    </div>
                    <div class="text-sm my-1">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                        @endfor
                    </div>
                    @if($r->comment)<p class="text-sm text-cocoa/80">{{ $r->comment }}</p>@endif
                </div>
            @empty
                <p class="text-sm text-cocoa/50">Belum ada ulasan untuk menu ini.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const form     = document.getElementById('orderForm');
const inputs   = [...form.querySelectorAll('.qty-input')];
const totalEl  = document.getElementById('totalPrice');
const countEl  = document.getElementById('totalCount');
const submitBtn = document.getElementById('submitBtn');

function calc() {
    const addon = [...form.querySelectorAll('input[data-addon]:checked')]
        .reduce((s, i) => s + Number(i.dataset.addon), 0);

    let total = 0, count = 0;
    inputs.forEach(i => {
        const q = Math.max(0, Math.min(99, Number(i.value) || 0));
        total += (Number(i.dataset.price) + addon) * q;
        count += q;
    });

    totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    countEl.textContent = count + ' porsi';
    if (!submitBtn.dataset.locked) submitBtn.disabled = count === 0;
}

form.addEventListener('input', calc);
form.addEventListener('change', calc);
form.querySelectorAll('.step').forEach(b => b.addEventListener('click', () => {
    const el = document.getElementById(b.dataset.target);
    el.value = Math.max(0, Math.min(99, (Number(el.value) || 0) + Number(b.dataset.step)));
    calc();
}));
calc();
</script>
@endpush