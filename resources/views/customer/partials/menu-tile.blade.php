@php
    $habis = $p->stock <= 0 || ! $p->is_available;
@endphp
<a href="{{ route('customer.menu.show', $p) }}"
   class="group block bg-white p-2 sm:p-2.5 rounded-md shadow-md hover:-translate-y-1 hover:shadow-xl transition">

    <div class="relative aspect-[4/3] overflow-hidden bg-brand-soft flex items-center justify-center">
        @if($p->image)
            <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <span class="text-5xl">{{ category_icon($p->category?->name) }}</span>
        @endif

        @if($habis)
            <span class="absolute inset-0 bg-black/45 flex items-center justify-center text-white text-xs font-extrabold tracking-widest">HABIS</span>
        @endif
    </div>

    <p class="mt-2 text-center text-[11px] sm:text-xs font-extrabold leading-tight line-clamp-2 min-h-[2.4em]">{{ $p->name }}</p>

    <div class="mt-1 flex items-center justify-center gap-2 text-[11px]">
        <span class="font-bold text-brand">@if($p->variants_count)<small class="font-semibold text-cocoa/60">Mulai </small>@endif{{ rupiah($p->price) }}</span>
        <span class="text-cocoa/60">
            <i class="fa-solid fa-star text-amber-400"></i>
            {{ $p->reviews_count ? number_format($p->reviews_avg_rating, 1) : '-' }}
        </span>
    </div>
</a>