@extends('layouts.merchant')
@section('title', 'Produk')

@section('content')
<div class="card max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-extrabold">Kategori Produk</h2>
        <a href="{{ route('merchant.products.index') }}" class="text-xs font-bold text-brand">← Kembali ke Produk</a>
    </div>

    <form method="POST" action="{{ route('merchant.categories.store') }}" class="flex gap-2 mb-5">
        @csrf
        <input name="name" placeholder="Nama kategori baru" required class="input flex-1">
        <button class="btn btn-brand">Tambah</button>
    </form>

    <div class="divide-y divide-orange-100">
        @forelse($categories as $c)
            <div class="py-3 flex flex-wrap items-center gap-2">
                <form method="POST" action="{{ route('merchant.categories.update', $c) }}" class="flex gap-2 flex-1 min-w-[200px]">
                    @csrf @method('PUT')
                    <input name="name" value="{{ $c->name }}" class="input flex-1">
                    <button class="btn btn-ghost">Simpan</button>
                </form>
                <span class="text-[11px] text-cocoa/60">{{ $c->products_count }} produk</span>
                <form method="POST" action="{{ route('merchant.categories.destroy', $c) }}"
                      onsubmit="return confirm('Hapus kategori ini?')">
                    @csrf @method('DELETE')
                    <button class="text-xs font-bold text-red-600"><i class="fa-solid fa-trash"></i> Hapus</button>
                </form>
            </div>
        @empty
            <p class="py-4 text-sm text-cocoa/50">Belum ada kategori.</p>
        @endforelse
    </div>
</div>
@endsection