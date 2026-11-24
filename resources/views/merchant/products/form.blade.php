@extends('layouts.merchant')
@section('title', 'Produk')

@section('content')
@php
    $variants = old('variants', $product->variants->map(fn ($v) => ['name' => $v->name, 'price' => $v->price])->all());
    $addons   = old('addons',   $product->addons->map(fn ($a) => ['name' => $a->name, 'price' => $a->price])->all());
@endphp

<form method="POST" enctype="multipart/form-data"
      action="{{ $product->exists ? route('merchant.products.update', $product) : route('merchant.products.store') }}"
      class="card max-w-3xl mx-auto space-y-4">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <h2 class="text-lg font-extrabold">{{ $product->exists ? 'Ubah Produk' : 'Tambah Produk Baru' }}</h2>

    <div>
        <label class="lbl">Nama Produk</label>
        <input name="name" value="{{ old('name', $product->name) }}" required class="input">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="lbl">Kategori</label>
            <select name="category_id" class="input">
                <option value="">- Tanpa kategori -</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="lbl">Harga (Rp)</label>
            <input type="number" name="price" min="0" value="{{ old('price', $product->price) }}" class="input">
        </div>
        <div>
            <label class="lbl">Stok (porsi)</label>
            <input type="number" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}" required class="input">
        </div>
    </div>
    <p class="text-[11px] text-cocoa/60 -mt-2">
        Jika produk punya varian di bawah, kolom harga diabaikan dan harga tampil sebagai "Mulai dari" varian termurah.
    </p>

    <div>
        <label class="lbl">Deskripsi</label>
        <textarea name="description" rows="3" class="input">{{ old('description', $product->description) }}</textarea>
    </div>

    <div>
        <label class="lbl">Foto</label>
        @include('partials.image-input', ['name' => 'image', 'current' => $product->image])
    </div>

    {{-- VARIAN ISI --}}
    <div class="rounded-xl border border-orange-100 p-4">
        <div class="flex items-center justify-between mb-1">
            <h4 class="font-bold text-sm">Varian Isi / Ukuran</h4>
            <button type="button" onclick="addRow('variants')" class="text-xs font-bold text-brand">+ Tambah varian</button>
        </div>
        <p class="text-[11px] text-cocoa/60 mb-3">
            Contoh untuk Dimsum Mentai: <b>Isi 4</b> Rp25.000, <b>Isi 6</b> Rp35.000, <b>Isi 8</b> Rp45.000.
            Pelanggan dapat memilih beberapa varian sekaligus.
        </p>
        <div id="variants" class="space-y-2">
            @foreach($variants as $i => $r)
                <div class="flex gap-2">
                    <input name="variants[{{ $i }}][name]" value="{{ $r['name'] }}" placeholder="Nama varian (mis. Isi 4)" class="input flex-1">
                    <input type="number" min="0" name="variants[{{ $i }}][price]" value="{{ $r['price'] }}" placeholder="Harga (Rp)" class="input !w-32">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 px-1">✕</button>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ADD-ON --}}
    <div class="rounded-xl border border-orange-100 p-4">
        <div class="flex items-center justify-between mb-1">
            <h4 class="font-bold text-sm">Add-on (tambahan)</h4>
            <button type="button" onclick="addRow('addons')" class="text-xs font-bold text-brand">+ Tambah add-on</button>
        </div>
        <p class="text-[11px] text-cocoa/60 mb-3">Contoh: Telur +Rp3.000, Saus Mentai Extra +Rp5.000.</p>
        <div id="addons" class="space-y-2">
            @foreach($addons as $i => $r)
                <div class="flex gap-2">
                    <input name="addons[{{ $i }}][name]" value="{{ $r['name'] }}" placeholder="Nama add-on" class="input flex-1">
                    <input type="number" min="0" name="addons[{{ $i }}][price]" value="{{ $r['price'] }}" placeholder="Harga (Rp)" class="input !w-32">
                    <button type="button" onclick="this.parentElement.remove()" class="text-red-600 px-1">✕</button>
                </div>
            @endforeach
        </div>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="btn btn-brand !px-5 !py-2.5 !text-sm">Simpan</button>
        <a href="{{ route('merchant.products.index') }}" class="btn btn-ghost !px-5 !py-2.5 !text-sm">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
let counter = Date.now();
function addRow(type) {
    const i = counter++;
    const ph = type === 'variants' ? 'Nama varian (mis. Isi 6)' : 'Nama add-on';
    document.getElementById(type).insertAdjacentHTML('beforeend', `
        <div class="flex gap-2">
            <input name="${type}[${i}][name]" placeholder="${ph}" class="input flex-1">
            <input type="number" min="0" name="${type}[${i}][price]" placeholder="Harga (Rp)" class="input !w-32">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 px-1">✕</button>
        </div>`);
}
</script>
@endpush