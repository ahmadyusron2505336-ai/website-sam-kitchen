@extends('layouts.customer')
@section('title', 'Beranda')
@section('bare', '1')

@section('content')
<div class="space-y-5">

    {{-- ============ HERO ============ --}}
    <section class="grid md:grid-cols-2 gap-8 items-center py-4 sm:py-8">
        <div class="text-white">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/15 text-xs font-bold">
                <span class="w-2 h-2 rounded-full {{ $isOpen ? 'bg-green-400' : 'bg-red-300' }}"></span>
                {{ $isOpen ? 'Buka sekarang' : 'Sedang tutup' }}@if($todayHours) · hari ini {{ $todayHours }}@endif
            </span>

            <h1 class="mt-4 text-4xl sm:text-5xl font-extrabold leading-[1.1]">
                Lapar? Pesan menu favoritmu di <span class="text-amber-200">SAM Kitchen</span>
            </h1>
            <p class="mt-4 max-w-md text-sm sm:text-base text-white/90">
                Dimsum, kripik pisang, sambal, dan kopi pilihan. Pilih menu dan add-on, lalu pesan tanpa perlu daftar akun.
            </p>

            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('customer.menu') }}" class="btn !bg-white !text-brand !px-6 !py-3 !text-sm shadow-lg">
                    <i class="fa-solid fa-utensils"></i> Pesan Sekarang
                </a>
                <a href="{{ wa_link('Halo SAM Kitchen, saya ingin bertanya.') }}" target="_blank" rel="noopener"
                   class="btn !bg-[#25D366] !px-6 !py-3 !text-sm shadow-lg">
                    <i class="fa-brands fa-whatsapp"></i> Chat WhatsApp
                </a>
            </div>

            <div class="mt-8 grid grid-cols-3 gap-3 max-w-md">
                <div class="rounded-2xl bg-white/15 p-3 text-center">
                    <p class="text-2xl font-extrabold">{{ $menuCount }}</p>
                    <p class="text-[11px] text-white/85">Pilihan Menu</p>
                </div>
                <div class="rounded-2xl bg-white/15 p-3 text-center">
                    <p class="text-2xl font-extrabold">{{ number_format($orderDone, 0, ',', '.') }}</p>
                    <p class="text-[11px] text-white/85">Pesanan Selesai</p>
                </div>
                <div class="rounded-2xl bg-white/15 p-3 text-center">
                    <p class="text-2xl font-extrabold"><i class="fa-solid fa-star text-amber-300 text-lg"></i> {{ $avgRating ? number_format((float) $avgRating, 1) : '-' }}</p>
                    <p class="text-[11px] text-white/85">Rating</p>
                </div>
            </div>
        </div>

        {{-- Kolase menu favorit --}}
        <div class="relative">
            <span class="absolute -top-3 left-4 z-10 px-3 py-1 rounded-full bg-cocoa text-white text-xs font-bold shadow-lg">
                <i class="fa-solid fa-fire text-amber-300"></i> Menu Favorit
            </span>

            <div class="bg-cream rounded-3xl p-3 sm:p-4 shadow-2xl md:rotate-2 hover:rotate-0 transition duration-300">
                @if($featured->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($featured as $p)
                            <a href="{{ route('customer.menu.show', $p) }}" class="group block">
                                <div class="aspect-square rounded-2xl overflow-hidden bg-brand-soft flex items-center justify-center">
                                    @if($p->image)
                                        <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}"
                                             class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                                    @else
                                        <span class="text-5xl">{{ category_icon($p->category?->name) }}</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 text-xs font-extrabold truncate">{{ $p->name }}</p>
                                <p class="text-[11px] font-bold text-brand">@if($p->variants_count)<span class="font-semibold text-cocoa/60">Mulai </span>@endif{{ rupiah($p->price) }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="aspect-square flex items-center justify-center text-7xl">🍽️</div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============ BANNER TOKO (jika ada) ============ --}}
    @if($store->banner)
        <section>
            <img src="{{ asset('storage/'.$store->banner) }}" alt="Banner SAM Kitchen"
                 class="w-full h-40 sm:h-64 object-cover rounded-3xl shadow-lg">
        </section>
    @endif

    {{-- ============ KATEGORI ============ --}}
    <section class="bg-cream rounded-3xl shadow-lg p-5 sm:p-7">
        <div class="mb-4">
            <h2 class="text-xl sm:text-2xl font-extrabold">Kategori Makanan</h2>
            <p class="text-sm text-cocoa/60">Pilih kategori favoritmu.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($categories as $c)
                <a href="{{ route('customer.menu', ['category' => $c->id]) }}"
                   class="group bg-white rounded-2xl border border-orange-100 p-4 text-center hover:-translate-y-1 hover:shadow-lg hover:border-brand transition">
                    <div class="w-14 h-14 mx-auto rounded-full bg-brand-soft flex items-center justify-center text-3xl group-hover:scale-110 transition">
                        {{ category_icon($c->name) }}
                    </div>
                    <p class="mt-2 font-bold text-sm">{{ $c->name }}</p>
                    <p class="text-[11px] text-cocoa/50">{{ $c->products_count }} menu</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- ============ MENU FAVORIT ============ --}}
    <section class="bg-cream rounded-3xl shadow-lg p-5 sm:p-7">
        <div class="flex items-end justify-between mb-4 gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-extrabold">Menu Favorit</h2>
                <p class="text-sm text-cocoa/60">Paling banyak diulas pelanggan.</p>
            </div>
            <a href="{{ route('customer.menu') }}" class="text-sm font-bold text-brand hover:underline whitespace-nowrap">Lihat semua →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse($featured as $p)
                @include('customer.partials.product-card', ['p' => $p])
            @empty
                <p class="text-sm text-cocoa/50">Belum ada menu tersedia.</p>
            @endforelse
        </div>
    </section>

    {{-- ============ CARA MEMESAN ============ --}}
    <section class="bg-cream rounded-3xl shadow-lg p-5 sm:p-7">
        <h2 class="text-xl sm:text-2xl font-extrabold mb-5">Cara Memesan</h2>
        <div class="grid md:grid-cols-3 gap-4">
            @foreach([
                ['1', 'Pilih Menu', 'Telusuri katalog, pilih varian porsi dan add-on, lalu masukkan ke keranjang.', 'fa-utensils'],
                ['2', 'Checkout', 'Isi nama dan alamat, lalu pilih pembayaran: Transfer, QRIS, atau COD.', 'fa-credit-card'],
                ['3', 'Pantau & Ulas', 'Simpan tautan pesanan untuk memantau status, lalu beri rating setelah selesai.', 'fa-star'],
            ] as [$n, $title, $desc, $icon])
                <div class="relative rounded-2xl bg-white border border-orange-100 p-5 pt-8">
                    <span class="absolute -top-4 left-5 w-9 h-9 rounded-full bg-brand text-white font-extrabold flex items-center justify-center shadow">{{ $n }}</span>
                    <p class="font-bold"><i class="fa-solid {{ $icon }} text-brand mr-1.5"></i>{{ $title }}</p>
                    <p class="mt-1 text-sm text-cocoa/70">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ ULASAN TERBARU ============ --}}
    @if($latestReviews->isNotEmpty())
        <section class="bg-cream rounded-3xl shadow-lg p-5 sm:p-7">
            <h2 class="text-xl sm:text-2xl font-extrabold mb-4">Kata Pelanggan</h2>
            <div class="grid md:grid-cols-3 gap-4">
                @foreach($latestReviews as $r)
                    <figure class="rounded-2xl bg-white border border-orange-100 p-5">
                        <div class="text-sm mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $r->rating ? 'text-amber-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                        <blockquote class="text-sm text-cocoa/80 line-clamp-4">“{{ $r->comment }}”</blockquote>
                        <figcaption class="mt-3 text-xs">
                            <b>{{ $r->reviewer_name }}</b>
                            <span class="text-cocoa/50">· {{ $r->product?->name }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============ LOKASI & KONTAK ============ --}}
    <section class="bg-cocoa text-white rounded-3xl shadow-lg p-6 sm:p-8 grid md:grid-cols-2 gap-6 items-center">
        <div>
            <h2 class="text-xl sm:text-2xl font-extrabold">Kunjungi atau Hubungi Kami</h2>
            <p class="mt-2 text-sm text-white/80"><i class="fa-solid fa-location-dot text-amber-300 mr-1.5"></i>{{ $store->address ?: 'Alamat belum diisi.' }}</p>
            @if($todayHours)
                <p class="mt-1 text-sm text-white/80"><i class="fa-regular fa-clock text-amber-300 mr-1.5"></i>Hari ini {{ $todayHours }}</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-3 md:justify-end">
            <a href="{{ route('customer.location') }}" class="btn btn-brand"><i class="fa-solid fa-route"></i> Lihat Lokasi</a>
            <a href="https://www.instagram.com/sam.kitchen01" target="_blank" rel="noopener" class="btn !bg-white/15 hover:!bg-white/25"><i class="fa-brands fa-instagram"></i> Instagram</a>
            <a href="https://www.tiktok.com/@@sam.kitchen06" target="_blank" rel="noopener" class="btn !bg-white/15 hover:!bg-white/25"><i class="fa-brands fa-tiktok"></i> TikTok</a>
        </div>
    </section>
</div>
@endsection