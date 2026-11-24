<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') | SAM Kitchen</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                fontFamily: { sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
                colors: {
                    brand: { DEFAULT: '#D95D2B', dark: '#B94A1E', soft: '#FDEBDD' },
                    cocoa: '#4A2C1A', cream: '#FFFDF8', sand: '#FAF6EE',
                },
            } },
        };
    </script>
    <style>
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:700;color:#fff;cursor:pointer;border:0;transition:.15s;white-space:nowrap}
        .btn-brand{background:#D95D2B}.btn-brand:hover{background:#B94A1E}
        .btn-brown{background:#4A2C1A}.btn-brown:hover{background:#33200f}
        .btn-ghost{background:#F1ECE3;color:#4A2C1A}.btn-ghost:hover{background:#e8e0d2}
        .btn:disabled{opacity:.5;cursor:not-allowed}
        .input{width:100%;border:1px solid #eadfce;border-radius:12px;padding:10px 12px;font-size:14px;background:#fff;color:#4A2C1A}
        .input:focus{outline:2px solid rgba(217,93,43,.25);border-color:#D95D2B}
        .lbl{display:block;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6b5646;margin-bottom:5px}
        .badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .badge-ok{background:#D1FAE5;color:#047857}.badge-low{background:#FEE2E2;color:#B91C1C}
        .pill{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .pill::before{content:'';width:8px;height:8px;border-radius:50%;background:currentColor}
        .pill-menunggu{background:#FEF3C7;color:#B45309}.pill-diproses{background:#DBEAFE;color:#1D4ED8}
        .pill-selesai{background:#D1FAE5;color:#047857}.pill-ditolak{background:#FEE2E2;color:#B91C1C}
        .pill-dibatalkan { background: #f3f4f6; color: #4b5563; }

        /* Pilihan bintang (tanpa JavaScript) */
        .stars{position:relative;display:inline-flex;flex-direction:row-reverse;gap:2px}
        .stars input{position:absolute;opacity:0;pointer-events:none}
        .stars label{cursor:pointer;font-size:26px;line-height:1;color:#d6cbbd}
        .stars label:hover,.stars label:hover ~ label,.stars input:checked ~ label{color:#F5A524}
    </style>
</head>
<body class="font-sans text-cocoa bg-brand">
@php
    $store     = current_store();
    $cartCount = \App\Support\Cart::count();
    $isAdmin   = auth()->user()?->role === 'pengelola';
    $nav = [
        ['customer.home',     'Beranda', 'fa-house'],
        ['customer.menu',     'Menu',    'fa-utensils'],
        ['customer.location', 'Lokasi',  'fa-location-dot'],
        ['customer.history',  'Riwayat', 'fa-clock-rotate-left'],
    ];
@endphp

{{-- HEADER --}}
<header class="sticky top-0 z-40 bg-cream border-b border-orange-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between gap-3">
        <a href="{{ route('customer.home') }}" class="flex items-center gap-2.5 shrink-0">
            <span class="w-10 h-10 rounded-full bg-white shadow flex items-center justify-center overflow-hidden">
                @if($store?->logo)
                    <img src="{{ asset('storage/'.$store->logo) }}" class="w-full h-full object-cover" alt="">
                @else
                    <svg viewBox="0 0 64 64" class="w-6 h-6 text-cocoa" fill="none" stroke="currentColor"
                         stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 40a10 10 0 0 1-2-19 12 12 0 0 1 22-5 11 11 0 0 1 12 9 9 9 0 0 1-4 15"/><path d="M16 48h32"/>
                    </svg>
                @endif
            </span>
            <span class="leading-tight">
                <span class="block font-extrabold text-brand tracking-wide">SAM KITCHEN</span>
                <span class="block text-[8px] tracking-[.18em] font-semibold text-cocoa/70">DELICIOUS FOOD &amp; DRINK</span>
            </span>
        </a>

        <nav class="hidden md:flex items-center gap-1">
            @foreach($nav as [$route, $label, $icon])
                <a href="{{ route($route) }}"
                   class="px-4 py-2 rounded-full text-sm font-semibold {{ request()->routeIs($route.'*') ? 'bg-brand text-white' : 'hover:bg-brand-soft' }}">
                    <i class="fa-solid {{ $icon }} mr-1.5"></i>{{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('customer.cart') }}" aria-label="Keranjang"
               class="relative w-10 h-10 rounded-full hover:bg-brand-soft flex items-center justify-center">
                <i class="fa-solid fa-cart-shopping"></i>
                @if($cartCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-brand text-white text-[10px] font-bold flex items-center justify-center">{{ $cartCount }}</span>
                @endif
            </a>

            @if($isAdmin)
                <a href="{{ route('merchant.dashboard') }}" title="Buka dashboard merchant" class="btn btn-brand !rounded-full !py-2">
                    <i class="fa-solid fa-gauge"></i><span class="hidden sm:inline">Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" title="Masuk ke halaman admin" class="btn btn-brown !rounded-full !py-2">
                    <i class="fa-solid fa-right-to-bracket"></i><span class="hidden sm:inline">Masuk Admin</span>
                </a>
            @endif

            <button id="navBtn" type="button" aria-label="Menu" class="md:hidden w-10 h-10 rounded-full bg-sand">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>

    {{-- Menu HP --}}
    <nav id="mobileNav" class="hidden md:hidden border-t border-orange-100 bg-cream px-4 py-2">
        @foreach($nav as [$route, $label, $icon])
            <a href="{{ route($route) }}"
               class="block px-3 py-2.5 rounded-xl text-sm font-semibold {{ request()->routeIs($route.'*') ? 'bg-brand text-white' : '' }}">
                <i class="fa-solid {{ $icon }} w-5"></i> {{ $label }}
            </a>
        @endforeach
        <a href="{{ route('customer.cart') }}" class="block px-3 py-2.5 rounded-xl text-sm font-semibold">
            <i class="fa-solid fa-cart-shopping w-5"></i> Keranjang @if($cartCount > 0) ({{ $cartCount }}) @endif
        </a>
    </nav>
</header>

{{-- KONTEN --}}
<main class="pattern-food min-h-[60vh] py-4 sm:py-6">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 space-y-4">

        @if(session('success'))
            <div class="p-3 rounded-xl bg-green-100 text-green-800 text-sm font-medium shadow">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-3 rounded-xl bg-red-100 text-red-800 text-sm shadow">
                <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Halaman "bare" (Beranda, Menu) langsung di atas latar; halaman lain di dalam kartu krem --}}
        @hasSection('bare')
            @yield('content')
        @else
            <div class="bg-cream rounded-2xl shadow-lg overflow-hidden">
                @yield('content')
            </div>
        @endif
    </div>
</main>

{{-- STRIP KONTAK --}}
<footer class="bg-cream border-t border-orange-100">
    <div class="max-w-6xl mx-auto px-4 py-3 flex flex-wrap items-center justify-center sm:justify-around gap-x-6 gap-y-2 text-[11px] sm:text-xs font-semibold">
        <a href="mailto:officialsam.1807@gmail.com" class="hover:text-brand">
            <i class="fa-solid fa-envelope text-brand mr-1"></i> officialsam.1807@gmail.com
        </a>
        <a href="https://www.instagram.com/sam.kitchen01" target="_blank" rel="noopener" class="hover:text-brand">
            <i class="fa-brands fa-instagram text-brand mr-1"></i> sam.kitchen01
        </a>
        <a href="https://www.tiktok.com/@@sam.kitchen06" target="_blank" rel="noopener" class="hover:text-brand">
            <i class="fa-brands fa-tiktok text-brand mr-1"></i> sam.kitchen06
        </a>
    </div>
</footer>

{{-- Chat WhatsApp (floating action button) --}}
<a href="{{ wa_link('Halo SAM Kitchen, saya ingin bertanya.') }}" target="_blank" rel="noopener"
   aria-label="Chat WhatsApp" title="Chat WhatsApp dengan CS SAM Kitchen"
   class="fixed bottom-16 right-4 sm:bottom-5 sm:right-5 z-30 w-14 h-14 rounded-full bg-[#25D366] text-white text-3xl shadow-lg flex items-center justify-center hover:scale-105 transition">
    <i class="fa-brands fa-whatsapp"></i>
</a>

<script>
    document.getElementById('navBtn').addEventListener('click', () =>
        document.getElementById('mobileNav').classList.toggle('hidden'));
</script>
@stack('scripts')
</body>
</html>