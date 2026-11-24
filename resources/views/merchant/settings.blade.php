@extends('layouts.merchant')
@section('title', 'Pengaturan')

@section('content')
@php
    $authId  = auth()->id();
    $isOwner = (int) $merchant->user_id === (int) $authId;
@endphp

<div class="card">
    <h2 class="text-lg font-extrabold mb-4">Pengaturan Sistem Toko</h2>

    <div class="flex flex-wrap gap-2 pb-4 mb-5 border-b border-orange-100">
        <button type="button" data-tab="profil" class="tab-btn">Profil Restoran</button>
        <button type="button" data-tab="jam"    class="tab-btn">Jam Operasional</button>
        <button type="button" data-tab="bayar"  class="tab-btn">Metode Pembayaran</button>
        <button type="button" data-tab="akun"   class="tab-btn">Akun</button>
    </div>

    {{-- FORM PENGATURAN TOKO (tab 1-3) --}}
    <form id="settingsForm" method="POST" action="{{ route('merchant.settings.update') }}" enctype="multipart/form-data">
        @csrf @method('PUT')

        <section data-panel="profil" class="space-y-4 max-w-2xl">
            <div>
                <label class="lbl">Nama Restoran / Outlet</label>
                <input name="name" value="{{ old('name', $merchant->name) }}" required class="input">
            </div>
            <div>
                <label class="lbl">Alamat Fisik</label>
                <textarea name="address" rows="3" class="input">{{ old('address', $merchant->address) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="lbl">Nomor WhatsApp / Kontak</label>
                    <input name="whatsapp" value="{{ old('whatsapp', $merchant->whatsapp) }}" class="input">
                </div>
                <div>
                    <label class="lbl">Email Bisnis</label>
                    <input type="email" name="business_email" value="{{ old('business_email', $merchant->business_email) }}" class="input">
                </div>
            </div>

            <div>
                <label class="lbl">Logo Outlet</label>
                @include('partials.image-input', ['name' => 'logo', 'current' => $merchant->logo, 'round' => true])
            </div>

            <div>
                <label class="lbl">Banner Outlet</label>
                @include('partials.image-input', ['name' => 'banner', 'current' => $merchant->banner])
            </div>
        </section>

        <section data-panel="jam" class="hidden max-w-2xl">
            @foreach($days as $d)
                <div class="flex flex-wrap items-center gap-3 py-2.5 border-b border-orange-100">
                    <span class="w-20 text-sm font-bold capitalize">{{ $d }}</span>
                    <input type="time" name="hours[{{ $d }}][open]"  value="{{ old("hours.$d.open",  $hours[$d]['open']) }}"  class="input !w-32">
                    <span class="text-xs font-bold">s/d</span>
                    <input type="time" name="hours[{{ $d }}][close]" value="{{ old("hours.$d.close", $hours[$d]['close']) }}" class="input !w-32">
                    <label class="flex items-center gap-1.5 text-xs font-semibold">
                        <input type="checkbox" name="hours[{{ $d }}][closed]" value="1"
                               @checked(old("hours.$d.closed", $hours[$d]['closed']))> Tutup
                    </label>
                </div>
            @endforeach
        </section>

        <section data-panel="bayar" class="hidden space-y-4 max-w-2xl">
            <p class="text-xs text-cocoa/60">Data rekening bank untuk pencairan dana penjualan.</p>
            <div>
                <label class="lbl">Nama Bank</label>
                <input name="bank_name" value="{{ old('bank_name', $merchant->bank_name) }}" class="input">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="lbl">Nomor Rekening</label>
                    <input name="bank_account_number" value="{{ old('bank_account_number', $merchant->bank_account_number) }}" class="input">
                </div>
                <div>
                    <label class="lbl">Atas Nama</label>
                    <input name="bank_account_name" value="{{ old('bank_account_name', $merchant->bank_account_name) }}" class="input">
                </div>
                <div>
                    <label class="lbl">QRIS Toko</label>
                    @include('partials.image-input', ['name' => 'qris_image', 'current' => $merchant->qris_image, 'big' => true])
                    <p class="text-[11px] text-cocoa/50 mt-1">Unggah gambar kode QRIS. Pilihan QRIS di checkout aktif jika gambar ini ada.</p>
                </div>
            </div>    
        </section>

        <button class="btn btn-brand !px-6 !py-2.5 !text-sm mt-6">Simpan Perubahan</button>
    </form>

    {{-- TAB AKUN (di luar form di atas) --}}
    <section data-panel="akun" class="hidden space-y-6 max-w-3xl">

        {{-- Tambah akun --}}
        @if($isOwner)
            <form method="POST" action="{{ route('merchant.accounts.store') }}" class="rounded-xl bg-sand p-4 space-y-3">
                @csrf
                <input type="hidden" name="_form" value="create">
                <h3 class="font-bold text-sm"><i class="fa-solid fa-user-plus text-brand"></i> Tambah Akun Baru</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="lbl">Nama</label>
                        <input name="name" value="{{ old('_form') === 'create' ? old('name') : '' }}" required class="input">
                    </div>
                    <div>
                        <label class="lbl">Email</label>
                        <input type="email" name="email" value="{{ old('_form') === 'create' ? old('email') : '' }}" required class="input">
                    </div>
                    <div>
                        <label class="lbl">Password</label>
                        <input type="password" name="password" required minlength="6" autocomplete="new-password" class="input">
                    </div>
                    <div>
                        <label class="lbl">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required minlength="6" autocomplete="new-password" class="input">
                    </div>
                </div>
                <button class="btn btn-brand">Tambah Akun</button>
            </form>
        @else
            <p class="text-xs text-cocoa/60 rounded-xl bg-sand p-3">Hanya pemilik toko yang dapat menambah akun baru.</p>
        @endif

        {{-- Daftar akun + edit --}}
        <div>
            <h3 class="font-bold text-sm mb-3">Daftar Akun</h3>
            <div class="space-y-2">
                @foreach($accounts as $acc)
                    @php
                        $isSelf     = (int) $acc->id === (int) $authId;
                        $isAccOwner = (int) $acc->id === (int) $merchant->user_id;
                        $canEdit    = $isSelf || $isOwner;
                        $mine       = old('_form') === 'update:'.$acc->id;
                        $confirmMsg = "Hapus akun {$acc->name}? Riwayat pesanan yang pernah ditangani akun ini tidak akan menampilkan nama penanggung jawab lagi.";
                    @endphp

                    <details class="rounded-xl border border-orange-100 bg-white" @if($mine) open @endif>
                        <summary class="flex items-center gap-3 p-3 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                            <div class="w-9 h-9 shrink-0 rounded-full bg-cocoa text-white flex items-center justify-center text-sm">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold truncate">{{ $acc->name }}</p>
                                <p class="text-[11px] text-cocoa/60 truncate">{{ $acc->email }}</p>
                            </div>
                            @if($isSelf)<span class="badge bg-orange-100 text-brand">Anda</span>@endif
                            <span class="badge {{ $isAccOwner ? 'badge-ok' : 'bg-gray-100 text-gray-600' }}">
                                {{ $isAccOwner ? 'Pemilik' : 'Admin' }}
                            </span>
                            @if($canEdit)
                                <span class="text-xs font-bold text-brand whitespace-nowrap"><i class="fa-solid fa-pen"></i> Ubah</span>
                            @endif
                        </summary>

                        @if($canEdit)
                            <form method="POST" action="{{ route('merchant.accounts.update', $acc) }}"
                                  class="p-4 border-t border-orange-100 space-y-3">
                                @csrf @method('PUT')
                                <input type="hidden" name="_form" value="update:{{ $acc->id }}">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="lbl">Nama</label>
                                        <input name="name" value="{{ $mine ? old('name') : $acc->name }}" required class="input">
                                    </div>
                                    <div>
                                        <label class="lbl">Email</label>
                                        <input type="email" name="email" value="{{ $mine ? old('email') : $acc->email }}" required class="input">
                                    </div>
                                </div>

                                <p class="text-[11px] text-cocoa/60">Kosongkan kolom password jika tidak ingin mengubahnya.</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @if($isSelf)
                                        <div class="sm:col-span-2">
                                            <label class="lbl">Password Lama (wajib jika mengganti password)</label>
                                            <input type="password" name="current_password" autocomplete="current-password" class="input">
                                        </div>
                                    @endif
                                    <div>
                                        <label class="lbl">Password Baru</label>
                                        <input type="password" name="password" minlength="6" autocomplete="new-password" class="input">
                                    </div>
                                    <div>
                                        <label class="lbl">Konfirmasi Password Baru</label>
                                        <input type="password" name="password_confirmation" minlength="6" autocomplete="new-password" class="input">
                                    </div>
                                </div>

                                <button class="btn btn-brand">Simpan Akun</button>
                            </form>
                        @endif

                        {{-- TOMBOL HAPUS AKUN (form terpisah, tidak boleh di dalam form ubah) --}}
                        @if($isOwner && ! $isAccOwner)
                            <form method="POST" action="{{ route('merchant.accounts.destroy', $acc) }}"
                                  onsubmit="return confirm(@js($confirmMsg))"
                                  class="px-4 pb-4">
                                @csrf @method('DELETE')
                                <button class="btn btn-ghost !text-red-600">
                                    <i class="fa-solid fa-trash"></i> Hapus Akun
                                </button>
                            </form>
                        @endif
                    </details>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const tabs   = document.querySelectorAll('[data-tab]');
const panels = document.querySelectorAll('[data-panel]');
const form   = document.getElementById('settingsForm');

function showTab(k) {
    tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === k));
    panels.forEach(p => p.classList.toggle('hidden', p.dataset.panel !== k));
    form.classList.toggle('hidden', k === 'akun');   // tombol "Simpan Perubahan" hanya untuk tab 1-3
}
tabs.forEach(t => t.addEventListener('click', () => showTab(t.dataset.tab)));

const h = location.hash.replace('#', '');
const accountFormFailed = @json((bool) old('_form'));   // buka tab Akun jika validasi akun gagal
showTab(accountFormFailed ? 'akun' : (['profil', 'jam', 'bayar', 'akun'].includes(h) ? h : 'profil'));
</script>
@endpush