@extends('layouts.merchant')
@section('title', 'Produk')

@section('content')
<div class="card">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-lg font-extrabold">Manajemen Produk</h2>
            <p class="text-[11px] text-cocoa/60">Kelola menu makanan, minuman, dan persediaan stok toko Anda</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('merchant.categories.index') }}" class="btn btn-ghost"><i class="fa-solid fa-tags"></i> Kategori</a>
            <a href="{{ route('merchant.products.create') }}" class="btn btn-brand"><i class="fa-solid fa-plus"></i> Tambah Produk Baru</a>
        </div>
    </div>

    <form method="GET" class="grid grid-cols-1 md:grid-cols-[1fr_220px_220px] gap-3 mb-4">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-cocoa/40"></i>
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama makanan atau minuman..." class="input pl-9">
        </div>
        <select name="category" class="input" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="stock" class="input" onchange="this.form.submit()">
            <option value="">Semua Status Stok</option>
            <option value="tersedia" @selected(request('stock') === 'tersedia')>Tersedia (&gt; 10)</option>
            <option value="menipis"  @selected(request('stock') === 'menipis')>Menipis (1 - 10)</option>
            <option value="habis"    @selected(request('stock') === 'habis')>Habis</option>
        </select>
    </form>

    <table class="rtable">
        <thead>
            <tr><th>Foto</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th class="text-right">Aksi</th></tr>
        </thead>
        <tbody>
        @forelse($products as $p)
            <tr>
                <td data-label="Foto">
                    @if($p->image)
                        <img src="{{ asset('storage/'.$p->image) }}" class="w-9 h-9 rounded-lg object-cover" alt="">
                    @else
                        <span class="text-xl">🍽️</span>
                    @endif
                </td>
                <td data-label="Produk">
                    <div class="font-bold">{{ $p->name }}</div>
                    @if($p->variants->isNotEmpty())
                        <div class="text-[10px] font-semibold text-cocoa/50">{{ $p->variants->pluck('name')->implode(' · ') }}</div>
                    @endif
                </td>
                <td data-label="Kategori">{{ $p->category?->name ?? '-' }}</td>
                <td data-label="Harga" class="font-bold text-brand">
                    @if($p->variants->isNotEmpty())<small class="font-semibold text-cocoa/50">mulai </small>@endif{{ rupiah($p->price) }}
                </td>
                <td data-label="Stok">
                    <span class="badge {{ $p->stock_status === 'tersedia' ? 'badge-ok' : 'badge-low' }}">
                        {{ $p->stock_status === 'habis' ? 'Habis' : $p->stock.' porsi' }}
                    </span>
                </td>
                <td data-label="Aksi">
                    <div class="flex items-center justify-end gap-3 text-xs font-bold">
                        <a href="{{ route('merchant.products.edit', $p) }}" class="text-cocoa hover:underline">
                            <i class="fa-solid fa-pen"></i> Ubah
                        </a>
                        <form method="POST" action="{{ route('merchant.products.destroy', $p) }}"
                              onsubmit="return confirm('Hapus produk ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline"><i class="fa-solid fa-trash"></i> Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="text-center text-cocoa/50 py-6">Belum ada produk.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-4">{{ $products->links() }}</div>
</div>
@endsection