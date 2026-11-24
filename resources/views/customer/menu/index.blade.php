@extends('layouts.customer')
@section('title', 'Daftar Menu')
@section('bare', '1')

@section('content')
<div class="mb-4">
    <h1 class="text-white text-3xl sm:text-4xl font-extrabold">Daftar Menu</h1>
    <p class="text-white/85 text-sm">Harga terbaru, rating, dan ulasan dari pelanggan.</p>
</div>

{{-- Kategori --}}
<div class="mb-4 flex gap-2 overflow-x-auto pb-1">
    <a href="{{ route('customer.menu', array_filter(['q' => request('q')])) }}"
       class="shrink-0 px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow {{ ! request('category') ? 'bg-cocoa text-white' : 'bg-cream text-cocoa hover:bg-white' }}">Semua Menu</a>
    @foreach($categories as $c)
        <a href="{{ route('customer.menu', array_filter(['category' => $c->id, 'q' => request('q')])) }}"
           class="shrink-0 px-4 py-2 rounded-full text-xs sm:text-sm font-bold shadow {{ (int) request('category') === $c->id ? 'bg-cocoa text-white' : 'bg-cream text-cocoa hover:bg-white' }}">
            {{ category_icon($c->name) }} {{ $c->name }}
        </a>
    @endforeach
</div>

{{-- Kolom cari --}}
<form method="GET" action="{{ route('customer.menu') }}" class="flex items-center gap-3 mb-5">
    @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
    <input name="q" value="{{ request('q') }}" placeholder="Ketik disini"
           class="flex-1 min-w-0 rounded-full bg-cream px-6 py-3 text-sm shadow-md focus:outline-none focus:ring-2 focus:ring-white/60">
    <button aria-label="Cari"
            class="w-12 h-12 shrink-0 rounded-full bg-cream text-cocoa shadow-md flex items-center justify-center hover:bg-white transition">
        <i class="fa-solid fa-magnifying-glass"></i>
    </button>
</form>

{{-- Kartu menu --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
    @forelse($products as $p)
        @include('customer.partials.menu-tile', ['p' => $p])
    @empty
        <p class="col-span-full text-center text-white py-12 font-semibold">Menu tidak ditemukan.</p>
    @endforelse
</div>
@endsection