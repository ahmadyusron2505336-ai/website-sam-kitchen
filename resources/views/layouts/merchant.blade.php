<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Merchant Center') | SAM Kitchen</title>

    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        input::-ms-reveal, input::-ms-clear { display: none; }

        /* Komponen */
        .card{background:#FFFDF8;border-radius:16px;padding:16px;box-shadow:0 2px 10px rgba(74,44,26,.10)}
        @media(min-width:640px){.card{padding:22px}}
        .input{width:100%;border:1px solid #eadfce;border-radius:12px;padding:9px 12px;font-size:13px;background:#fff;color:#4A2C1A}
        .input:focus{outline:2px solid rgba(217,93,43,.25);border-color:#D95D2B}
        .lbl{display:block;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#6b5646;margin-bottom:5px}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:8px 14px;border-radius:10px;font-size:12px;font-weight:700;color:#fff;cursor:pointer;border:0;transition:.15s;white-space:nowrap}
        .btn-brand{background:#D95D2B}.btn-brand:hover{background:#B94A1E}
        .btn-brown{background:#4A2C1A}.btn-brown:hover{background:#33200f}
        .btn-green{background:#0F9D6B}.btn-green:hover{background:#0b7d55}
        .btn-red{background:#DC2626}
        .btn-ghost{background:#F1ECE3;color:#4A2C1A}.btn-ghost:hover{background:#e8e0d2}
        .tab-btn{padding:7px 16px;border-radius:10px;font-size:12px;font-weight:700;background:#F1ECE3;color:#4A2C1A;border:0;cursor:pointer}
        .tab-btn.active{background:#D95D2B;color:#fff}

        /* Status */
        .pill{display:inline-flex;align-items:center;gap:6px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .pill::before{content:'';width:8px;height:8px;border-radius:50%;background:currentColor}
        .pill-menunggu{background:#FEF3C7;color:#B45309}
        .pill-diproses{background:#DBEAFE;color:#1D4ED8}
        .pill-selesai{background:#D1FAE5;color:#047857}
        .pill-ditolak{background:#FEE2E2;color:#B91C1C}
        .pill-dibatalkan { background: #f3f4f6; color: #4b5563; }
        .badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700}
        .badge-ok{background:#D1FAE5;color:#047857}
        .badge-low{background:#FEE2E2;color:#B91C1C}

        /* Tabel: di layar lebar berupa tabel, di HP berubah jadi kartu */
        .rtable{width:100%;font-size:13px;border-collapse:collapse}
        .rtable thead th{background:#FBF6EC;color:#6b5646;font-size:10px;letter-spacing:.05em;text-transform:uppercase;text-align:left;padding:10px 12px;font-weight:700}
        .rtable tbody td{padding:12px;border-bottom:1px solid #f3ece0;vertical-align:middle}
        @media(max-width:767px){
            .rtable thead{display:none}
            .rtable,.rtable tbody,.rtable tr,.rtable td{display:block;width:100%}
            .rtable tr{border:1px solid #f0e6d8;border-radius:14px;padding:8px 14px;margin-bottom:10px;background:#fff}
            .rtable tbody td{display:flex;justify-content:space-between;align-items:center;gap:14px;padding:7px 0;border:0;text-align:right}
            .rtable tbody td::before{content:attr(data-label);font-weight:700;font-size:10px;text-transform:uppercase;letter-spacing:.04em;color:#8b7a6b;text-align:left;flex-shrink:0}
            .rtable tbody td[colspan]{justify-content:center}
        }

        /* Cetak / Simpan PDF */
        @media print{
            aside,header,.no-print,#overlay{display:none!important}
            main.pattern-food{background:#fff!important;padding:0!important}
            .card{box-shadow:none;border:1px solid #ddd;break-inside:avoid}
            body{background:#fff}
        }
    </style>
</head>
<body class="font-sans text-cocoa bg-brand">
@php
    $merchant = auth()->user()->merchant;
    $open = $merchant?->is_open ?? true;
    $menu = [
        ['merchant.dashboard',     ['merchant.dashboard'],                              'Dashboard',  'fa-chart-pie'],
        ['merchant.products.index',['merchant.products.*', 'merchant.categories.*'],    'Produk',     'fa-utensils'],
        ['merchant.orders.index',  ['merchant.orders.*'],                               'Pesanan',    'fa-clipboard-list'],
        ['merchant.reports.index', ['merchant.reports.*'],                              'Laporan',    'fa-file-invoice-dollar'],
        ['merchant.settings.edit', ['merchant.settings.*'],                             'Pengaturan', 'fa-gear'],
    ];
@endphp

@php
    $open = auth()->user()->merchant?->is_open ?? true;
@endphp

<div id="overlay" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

<div class="lg:flex min-h-screen">

    {{-- SIDEBAR: tersembunyi di HP (geser), tetap tampil di desktop --}}
    <aside id="sidebar"
           class="fixed top-0 left-0 h-screen w-64 z-40 -translate-x-full transition-transform duration-200
                  lg:translate-x-0 lg:sticky lg:w-60 lg:shrink-0 lg:self-start
                  bg-sand border-r border-orange-100 flex flex-col">
        <div class="px-6 py-6 text-center border-b border-orange-100">
            <div class="mx-auto w-16 h-16 rounded-full bg-white shadow flex items-center justify-center overflow-hidden">
                @if($merchant?->logo)
                    <img src="{{ asset('storage/'.$merchant->logo) }}" class="w-full h-full object-cover" alt="Logo">
                @else
                    <svg viewBox="0 0 64 64" class="w-9 h-9 text-cocoa" fill="none" stroke="currentColor"
                         stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 40a10 10 0 0 1-2-19 12 12 0 0 1 22-5 11 11 0 0 1 12 9 9 9 0 0 1-4 15"/>
                        <path d="M16 48h32"/>
                    </svg>
                @endif
            </div>
            <p class="mt-3 font-extrabold text-brand tracking-wide">SAM KITCHEN</p>
            <p class="text-[9px] tracking-[.18em] font-semibold text-cocoa/70">DELICIOUS FOOD &amp; DRINK</p>
        </div>

        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            @foreach($menu as [$route, $patterns, $label, $icon])
                @php $active = request()->routeIs(...$patterns); @endphp
                <a href="{{ route($route) }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition
                          {{ $active ? 'bg-brand text-white shadow' : 'text-cocoa hover:bg-brand-soft' }}">
                    <i class="fa-solid {{ $icon }} w-4 text-center"></i>
                    <span class="flex-1">{{ $label }}</span>
                    @if($label === 'Pesanan')
                        <span data-pending-badge
                              class="text-[10px] font-bold px-1.5 py-0.5 rounded-full
                                     {{ $active ? 'bg-white text-brand' : 'bg-brand text-white' }}
                                     {{ ($pendingCount ?? 0) > 0 ? '' : 'hidden' }}">{{ $pendingCount ?? 0 }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <div class="p-4 border-t border-orange-100 text-[11px] text-cocoa/80 space-y-1.5">
            <p class="truncate"><i class="fa-solid fa-envelope w-4 text-brand"></i> officialsam.1807@gmail.com</p>
            <p><i class="fa-brands fa-instagram w-4 text-brand"></i> sam.kitchen01</p>
            <p><i class="fa-brands fa-tiktok w-4 text-brand"></i> sam.kitchen06</p>
        </div>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">

        {{-- TOPBAR --}}
        <header class="sticky top-0 z-20 h-16 px-4 md:px-6 bg-cream border-b border-orange-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="menuBtn" type="button" aria-label="Buka menu"
                        class="lg:hidden w-10 h-10 rounded-xl bg-sand text-cocoa"><i class="fa-solid fa-bars"></i></button>
                <h1 class="font-bold text-sm sm:text-base md:text-lg">Selamat Datang Admin</h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
            <div class="flex items-center gap-2 sm:gap-4">
                {{-- Tombol buka/tutup toko --}}
                <form method="POST" action="{{ route('merchant.store.toggle') }}" class="shrink-0"
                      @if($open) onsubmit="return confirm('Tutup toko? Pelanggan tidak bisa memesan sampai toko dibuka lagi.')" @endif>
                    @csrf
                    <button type="submit" title="{{ $open ? 'Tutup toko' : 'Buka toko' }}"
                            class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-bold transition
                                   {{ $open
                                        ? 'border-red-200 bg-red-50 text-red-600 hover:bg-red-100'
                                        : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' }}">
                        <i class="fa-solid {{ $open ? 'fa-store-slash' : 'fa-store' }}"></i>
                        <span class="hidden sm:inline">{{ $open ? 'Tutup Toko' : 'Buka Toko' }}</span>
                    </button>
                </form>
              
                <a href="{{ route('merchant.orders.index', ['status' => 'menunggu']) }}"
                   class="relative w-9 h-9 flex items-center justify-center" title="Pesanan menunggu">                   
                    <i class="fa-regular fa-bell text-lg"></i>
                    <span data-pending-dot class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-brand {{ ($pendingCount ?? 0) > 0 ? '' : 'hidden' }}"></span>
                </a>
                <div class="hidden sm:block w-px h-8 bg-orange-100"></div>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-cocoa text-white flex items-center justify-center text-sm">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <p class="text-xs font-bold">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-cocoa/60">Pengelola</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="w-9 h-9 rounded-xl text-cocoa/70 hover:bg-brand-soft" title="Keluar">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </header>

        {{-- KONTEN --}}
        <main class="pattern-food flex-1 p-3 sm:p-5 lg:p-6">
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-green-100 text-green-800 text-sm font-medium">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="mb-4 p-3 rounded-xl bg-red-100 text-red-800 text-sm">
                    <ul class="list-disc ml-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    function toggleMenu(open) {
        sidebar.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);
    }
    document.getElementById('menuBtn').addEventListener('click', () => toggleMenu(true));
    overlay.addEventListener('click', () => toggleMenu(false));
</script>

{{-- Notifikasi pesanan baru (semua halaman admin) --}}
<a id="toast" href="{{ route('merchant.orders.index', ['status' => 'menunggu']) }}"
   class="hidden fixed top-20 right-4 z-50 max-w-xs bg-cocoa text-white rounded-2xl shadow-2xl p-4 text-sm">
    <p class="font-bold"><i class="fa-solid fa-bell text-amber-300"></i> Pesanan baru masuk!</p>
    <p class="text-xs text-white/80">Klik untuk melihat pesanan.</p>
</a>

<script>
(function () {
    const feedUrl = '{{ route('merchant.orders.feed') }}';
    let first = true, lastId = 0;

    async function poll() {
        try {
            const r = await fetch(feedUrl, { headers: { 'Accept': 'application/json' } });
            const d = await r.json();
            const latest = d.latest || 0;

            document.querySelectorAll('[data-pending-badge]').forEach(el => {
                el.textContent = d.waiting;
                el.classList.toggle('hidden', d.waiting === 0);
            });
            document.querySelectorAll('[data-pending-dot]').forEach(el =>
                el.classList.toggle('hidden', d.waiting === 0));

            if (!first && latest > lastId) {
                const t = document.getElementById('toast');
                t.classList.remove('hidden');
                setTimeout(() => t.classList.add('hidden'), 8000);
            }
            lastId = latest;
            first = false;
        } catch (e) {}
    }

    poll();
    setInterval(poll, 10000);
})();
</script>
<script src="{{ asset('js/password-toggle.js') }}"></script>
@stack('scripts')
</body>
</html>
