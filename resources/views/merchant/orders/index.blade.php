@extends('layouts.merchant')
@section('title', 'Pesanan')

@section('content')
@php
    $tabs = ['semua' => 'Semua', 'menunggu' => 'Menunggu', 'diproses' => 'Diproses', 'selesai' => 'Selesai'];
    if (($counts['ditolak'] ?? 0) > 0) $tabs['ditolak'] = 'Ditolak';
    if (($counts['dibatalkan'] ?? 0) > 0) $tabs['dibatalkan'] = 'Dibatalkan';
@endphp

<div class="card">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-lg font-extrabold">Daftar Pesanan Masuk</h2>
            <p class="text-[11px] text-cocoa/60">Pantau dan perbarui status pesanan pelanggan secara real-time</p>
        </div>

        <div class="flex flex-col items-start sm:items-end gap-2">
            <div class="flex gap-2">
                <span class="pill pill-menunggu">{{ $counts['menunggu'] ?? 0 }} Menunggu</span>
                <span class="pill pill-diproses">{{ $counts['diproses'] ?? 0 }} Diproses</span>
            </div>

            {{-- Aksi massal: Diproses dan Selesai muncul setelah ada pesanan yang dipilih --}}
            <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                <label class="flex items-center gap-1.5 text-[11px] font-semibold cursor-pointer">
                    <input type="checkbox" id="selectAll"> Pilih semua
                </label>

                <button type="submit" form="bulkForm" name="action" value="diproses" id="btnProses"
                        style="display:none" class="btn btn-brand">
                    <i class="fa-solid fa-fire-burner"></i> Diproses
                </button>
                <button type="submit" form="bulkForm" name="action" value="selesai" id="btnSelesai"
                        style="display:none" class="btn btn-green">
                    <i class="fa-solid fa-circle-check"></i> Selesai
                </button>
                <button type="submit" form="bulkForm" name="action" value="hapus" id="btnHapus" disabled
                        class="btn btn-red disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-trash"></i> Hapus Data (<span id="bulkCount">0</span>)
                </button>
            </div>
        </div>
    </div>

    {{-- Form kosong; checkbox di tabel terhubung lewat atribut form="bulkForm" --}}
    <form id="bulkForm" method="POST" action="{{ route('merchant.orders.bulk') }}" onsubmit="return confirmBulk(event)">
        @csrf
    </form>

    <div class="flex flex-wrap gap-2 pb-4 mb-3 border-b border-orange-100">
        @foreach($tabs as $key => $label)
            <a href="{{ route('merchant.orders.index', ['status' => $key]) }}"
               class="tab-btn {{ $status === $key ? 'active' : '' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
        @endforeach
    </div>

    <table class="rtable">
        <thead>
            <tr>
                <th class="w-8"></th><th>ID Pesanan</th><th>Nama Pelanggan</th><th>Item Dipesan</th>
                <th>Total Harga</th><th>Status</th><th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($orders as $o)
            <tr>
                <td data-label="Pilih">
                    <input type="checkbox" name="ids[]" value="{{ $o->id }}" form="bulkForm" class="order-check">
                </td>
                <td data-label="ID" class="font-bold">#{{ $o->code }}</td>
                <td data-label="Pelanggan" class="font-bold">{{ $o->customer_name }}</td>
                <td data-label="Item" class="text-cocoa/80 md:max-w-xs">{{ $o->items_summary }}</td>
                <td data-label="Total" class="font-bold text-brand">{{ rupiah($o->total) }}</td>
                <td data-label="Status"><span class="pill pill-{{ $o->status }}">{{ ucfirst($o->status) }}</span></td>
                <td data-label="Aksi">
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <a href="{{ route('merchant.orders.show', $o) }}" class="btn btn-ghost">Lihat Detail</a>

                        @if($o->status === 'menunggu')
                            <form method="POST" action="{{ route('merchant.orders.accept', $o) }}">
                                @csrf <button class="btn btn-brand">Terima</button>
                            </form>
                            <form method="POST" action="{{ route('merchant.orders.reject', $o) }}"
                                  onsubmit="this.reject_reason.value = prompt('Alasan menolak pesanan?') || ''; return this.reject_reason.value !== ''">
                                @csrf
                                <input type="hidden" name="reject_reason">
                                <button class="btn btn-ghost !text-red-600">Tolak</button>
                            </form>
                        @elseif($o->status === 'diproses')
                            <form method="POST" action="{{ route('merchant.orders.complete', $o) }}">
                                @csrf <button class="btn btn-green">Selesaikan</button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center text-cocoa/50 py-6">Tidak ada pesanan.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
const checks    = () => [...document.querySelectorAll('.order-check')];
const selectAll = document.getElementById('selectAll');
const btnProses = document.getElementById('btnProses');
const btnSelesai = document.getElementById('btnSelesai');
const btnHapus  = document.getElementById('btnHapus');
const countEl   = document.getElementById('bulkCount');

function refreshBulk() {
    const total = checks().length;
    const n = checks().filter(c => c.checked).length;

    countEl.textContent = n;
    btnHapus.disabled = n === 0;
    btnProses.style.display  = n > 0 ? '' : 'none';
    btnSelesai.style.display = n > 0 ? '' : 'none';

    selectAll.checked = total > 0 && n === total;
    selectAll.indeterminate = n > 0 && n < total;
}

selectAll.addEventListener('change', () => {
    checks().forEach(c => c.checked = selectAll.checked);
    refreshBulk();
});
checks().forEach(c => c.addEventListener('change', refreshBulk));
refreshBulk();

function confirmBulk(e) {
    const n = checks().filter(c => c.checked).length;
    const action = e.submitter ? e.submitter.value : '';
    if (n === 0) return false;

    if (action === 'hapus')    return confirm('Hapus ' + n + ' pesanan terpilih? Data pesanan beserta rinciannya akan hilang permanen.');
    if (action === 'diproses') return confirm('Terima ' + n + ' pesanan terpilih dan ubah statusnya menjadi Diproses? Hanya pesanan berstatus Menunggu yang diproses.');
    return confirm('Tandai ' + n + ' pesanan terpilih sebagai Selesai? Hanya pesanan berstatus Diproses yang diubah.');
}

// Pembaruan "real-time": muat ulang jika ada pesanan baru dan tidak ada yang sedang dicentang
let lastId = null;
async function poll() {
    try {
        const r = await fetch('{{ route('merchant.orders.feed') }}', { headers: { 'Accept': 'application/json' } });
        const d = await r.json();
        if (lastId !== null && d.latest !== lastId) {
            if (!checks().some(c => c.checked)) location.reload();
            return;
        }
        lastId = d.latest;
    } catch (e) {}
}
poll();
setInterval(poll, 10000);
</script>
@endpush