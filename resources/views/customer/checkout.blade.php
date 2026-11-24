@extends('layouts.customer')
@section('title', 'Checkout')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold">Checkout</h1>
    <p class="text-sm text-cocoa/60 mb-5">Isi data pengantaran dan pilih cara pembayaran.</p>

    <form method="POST" action="{{ route('customer.checkout.store') }}" class="grid md:grid-cols-5 gap-6">
        @csrf

        <div class="md:col-span-3 space-y-5">
            {{-- Data pengantaran --}}
            <div class="bg-white rounded-2xl border border-orange-100 p-5 space-y-4">
                <div>
                    <label class="lbl">Nama</label>
                    <input name="customer_name" value="{{ old('customer_name') }}" required class="input">
                    @error('customer_name') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="lbl">Alamat (jalan, nomor rumah, patokan)</label>
                    <textarea name="address" rows="2" maxlength="255" required
                              placeholder="Contoh: Jl. Ijen No. 12, depan toko kelontong"
                              class="input">{{ old('address') }}</textarea>
                    @error('address') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="lbl">RT</label>
                        <input name="rt" value="{{ old('rt') }}" required inputmode="numeric" maxlength="3"
                               placeholder="001" class="input">
                        @error('rt') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="lbl">RW</label>
                        <input name="rw" value="{{ old('rw') }}" required inputmode="numeric" maxlength="3"
                               placeholder="002" class="input">
                        @error('rw') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="lbl">Kelurahan / Desa</label>
                        <input name="kelurahan" value="{{ old('kelurahan') }}" required maxlength="100" class="input">
                        @error('kelurahan') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="lbl">Kecamatan</label>
                        <input name="kecamatan" value="{{ old('kecamatan') }}" required maxlength="100" class="input">
                        @error('kecamatan') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="lbl">Kota / Kabupaten</label>
                        <input name="city" value="{{ old('city') }}" required maxlength="100" class="input">
                        @error('city') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="lbl">Kode Pos</label>
                        <input name="postal_code" value="{{ old('postal_code') }}" required inputmode="numeric" maxlength="5"
                               placeholder="65145" class="input">
                        @error('postal_code') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="lbl">Catatan (opsional)</label>
                    <textarea name="note" rows="2" maxlength="255" placeholder="Contoh: jangan terlalu pedas" class="input">{{ old('note') }}</textarea>
                </div>
            </div>

            {{-- Metode pembayaran --}}
            <div class="bg-white rounded-2xl border border-orange-100 p-5">
                <p class="lbl">Metode Pembayaran</p>
                <div class="space-y-2">
                    @foreach($methods as $key => $m)
                        <label class="flex items-start gap-3 p-3 rounded-xl border border-orange-100
                                      {{ $m['enabled'] ? 'cursor-pointer has-[:checked]:border-brand has-[:checked]:bg-brand-soft' : 'opacity-50 cursor-not-allowed' }}">
                            <input type="radio" name="payment_method" value="{{ $key }}"
                                   class="mt-1 accent-[#D95D2B]"
                                   @checked(old('payment_method') === $key)
                                   @if($m['enabled']) required @else disabled @endif>
                            <span class="flex-1">
                                <span class="block text-sm font-bold">
                                    <i class="fa-solid {{ $m['icon'] }} w-5 text-brand"></i> {{ $m['label'] }}
                                </span>
                                <span class="block text-[11px] text-cocoa/60">{{ $m['enabled'] ? $m['desc'] : 'Belum tersedia' }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="md:col-span-2 bg-white rounded-2xl border border-orange-100 p-5 h-fit">
            <h2 class="font-bold mb-3">Ringkasan Pesanan</h2>
            <ul class="divide-y divide-orange-100 text-sm">
                @foreach($lines as $l)
                    <li class="py-2.5 flex justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $l['qty'] }}× {{ $l['product']->name }}</p>
                            <p class="text-[11px] text-cocoa/60">
                                {{ $l['variant']?->name ?? 'Standar' }}
                                @if($l['addons']->count()) · + {{ $l['addons']->pluck('name')->implode(', ') }} @endif
                            </p>
                        </div>
                        <span class="font-bold whitespace-nowrap">{{ rupiah($l['subtotal']) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="flex justify-between items-center pt-3 mt-1 border-t border-orange-100">
                <span class="font-bold">Total</span>
                <span class="text-xl font-extrabold text-brand">{{ rupiah($total) }}</span>
            </div>

            <button class="btn btn-brand w-full !py-3 !text-sm mt-4">
                <i class="fa-solid fa-paper-plane"></i> Kirim Pesanan
            </button>
            <a href="{{ route('customer.cart') }}" class="block mt-3 text-center text-xs font-bold text-brand">← Ubah keranjang</a>
        </div>
    </form>
</div>
@endsection