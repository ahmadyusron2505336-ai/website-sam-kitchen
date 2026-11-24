@php
    $habis = $p->stock <= 0 || ! $p->is_available;
@endphp
<a href="{{ route('customer.menu.show', $p) }}"
   class="group block bg-white rounded-2xl overflow-hidden border border-orange-100 shadow-sm hover:shadow-lg transition">
    <div class="relative aspect-[4/3] bg-brand-soft flex items-center justify-center overflow-hidden">
        @if($p->image)
            <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->name }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
        @else
            <span class="text-6xl">{{ category_icon($p->category?->name) }}</span>
        @endif
        @if($habis)<span class="absolute top-2 left-2 badge badge-low">Habis</span>@endif
    </div>
    <div class="p-4">
        <p class="text-[11px] font-bold text-brand uppercase tracking-wide">{{ $p->category?->name ?? 'Menu' }}</p>
        <h3 class="font-bold truncate">{{ $p->name }}</h3>
        <div class="flex items-center gap-1 text-xs mt-1">
            <i class="fa-solid fa-star text-amber-400"></i>
            @if($p->reviews_count)
                <b>{{ number_format($p->reviews_avg_rating, 1) }}</b>
                <span class="text-cocoa/50">({{ $p->reviews_count }} ulasan)</span>
            @else
                <span class="text-cocoa/50">Belum ada ulasan</span>
            @endif
        </div>
        <div class="flex items-center justify-between mt-3">
            <span class="font-extrabold text-brand">
                @if($p->variants_count)<small class="font-semibold text-cocoa/60">Mulai </small>@endif{{ rupiah($p->price) }}
            </span>
            <span class="btn btn-brand !py-1.5 !text-xs">{{ $habis ? 'Lihat' : 'Pilih' }}</span>
        </div>
    </div>
</a>