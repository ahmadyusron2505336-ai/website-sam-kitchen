@extends('layouts.customer')
@section('title', 'Lokasi')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold">Lokasi Kami</h1>
    <p class="text-sm text-cocoa/60">Temukan SAM Kitchen dan dapatkan petunjuk arah.</p>

    <div class="mt-5 grid lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 rounded-2xl overflow-hidden border border-orange-100 bg-white">
            <iframe src="{{ $embed }}" title="Peta lokasi SAM Kitchen" loading="lazy" allowfullscreen
                    referrerpolicy="no-referrer-when-downgrade" class="w-full h-80 sm:h-[26rem] border-0"></iframe>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-orange-100 p-5">
                <p class="lbl">Alamat Lengkap</p>
                <p class="text-sm font-semibold">{{ $store->address ?: 'Alamat belum diisi.' }}</p>

                <div class="mt-4 flex flex-col gap-2">
                    <a href="{{ $directions }}" target="_blank" rel="noopener" class="btn btn-brand"><i class="fa-solid fa-route"></i> Petunjuk Arah</a>
                    <a href="{{ wa_link('Halo SAM Kitchen, saya ingin menanyakan lokasi.') }}" target="_blank" rel="noopener"
                       class="btn !bg-[#25D366]"><i class="fa-brands fa-whatsapp"></i> Tanya via WhatsApp</a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-orange-100 p-5">
                <p class="lbl">Jam Operasional</p>
                <ul class="text-sm divide-y divide-orange-100">
                    @foreach($days as $d)
                        @php $h = $hours[$d] ?? null; @endphp
                        <li class="flex justify-between py-1.5">
                            <span class="capitalize font-semibold">{{ $d }}</span>
                            <span class="{{ ($h['closed'] ?? false) ? 'text-red-600 font-semibold' : '' }}">
                                {{ $h ? (($h['closed'] ?? false) ? 'Tutup' : $h['open'].' - '.$h['close']) : '-' }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection