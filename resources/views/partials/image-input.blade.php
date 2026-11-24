@php
    $current = $current ?? null;
    $round   = $round ?? false;
    $url     = $current ? asset('storage/'.$current) : '';
@endphp

<div data-img-input class="flex flex-wrap items-center gap-4">
        <div class="{{ $round ? 'w-14 h-14 rounded-full' : (($big ?? false) ? 'w-28 h-28 rounded-xl' : 'w-24 h-16 rounded-xl') }} shrink-0 overflow-hidden bg-sand border border-orange-100 flex items-center justify-center">
        <img data-preview data-original="{{ $url }}" alt=""
             @if($url) src="{{ $url }}" @endif
             class="w-full h-full object-cover {{ $url ? '' : 'hidden' }}">
        <i data-placeholder class="fa-solid fa-image text-cocoa/40 {{ $url ? 'hidden' : '' }}"></i>
    </div>

    <div class="min-w-0 space-y-2">
        <input type="file" name="{{ $name }}" accept="image/*" data-file
               class="block text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-brand file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-white">

        <button type="button" data-remove
                class="btn btn-ghost !text-red-600 {{ $url ? '' : 'hidden' }}">
            <i class="fa-solid fa-trash"></i> Hapus
        </button>

        <input type="hidden" name="remove_{{ $name }}" value="0" data-flag>
    </div>
</div>

@once
@push('scripts')
<script>
document.querySelectorAll('[data-img-input]').forEach(box => {
    const file = box.querySelector('[data-file]');
    const img  = box.querySelector('[data-preview]');
    const ph   = box.querySelector('[data-placeholder]');
    const btn  = box.querySelector('[data-remove]');
    const flag = box.querySelector('[data-flag]');
    const hadOriginal = !!img.dataset.original;

    function show(src) {
        if (src) {
            img.src = src;
            img.classList.remove('hidden');
            ph.classList.add('hidden');
            btn.classList.remove('hidden');
        } else {
            img.removeAttribute('src');
            img.classList.add('hidden');
            ph.classList.remove('hidden');
            btn.classList.add('hidden');
        }
    }

    // Pilih file baru: tampilkan pratinjau, batalkan penandaan hapus
    file.addEventListener('change', () => {
        if (file.files[0]) {
            flag.value = '0';
            show(URL.createObjectURL(file.files[0]));
        } else {
            show(flag.value === '1' ? '' : img.dataset.original);
        }
    });

    // Tombol Hapus: kosongkan input, tandai gambar lama untuk dihapus saat disimpan
    btn.addEventListener('click', () => {
        file.value = '';
        flag.value = hadOriginal ? '1' : '0';
        show('');
    });
});
</script>
@endpush
@endonce