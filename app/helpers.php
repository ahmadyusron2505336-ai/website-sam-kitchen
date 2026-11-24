<?php

if (! function_exists('rupiah')) {
    function rupiah($angka)
    {
        return 'Rp ' . number_format($angka ?? 0, 0, ',', '.');
    }
}

if (! function_exists('current_store')) {
    // Toko yang ditampilkan di sisi pelanggan (satu toko: SAM Kitchen)
    function current_store()
    {
        static $store = false;
        if ($store === false) {
            $store = \App\Models\Merchant::first();
        }
        return $store;
    }
}

if (! function_exists('wa_link')) {
    // Tautan WhatsApp dari nomor di Pengaturan (0812... otomatis menjadi 62812...)
    function wa_link(?string $text = null): string
    {
        $n = preg_replace('/\D+/', '', (string) (current_store()?->whatsapp ?? ''));
        if (str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1);
        }
        return 'https://wa.me/' . $n . ($text ? '?text=' . rawurlencode($text) : '');
    }
}

if (! function_exists('category_icon')) {
    function category_icon(?string $name): string
    {
        $map = ['dimsum' => '🥟', 'kripik' => '🍌', 'snack' => '🍌', 'sambal' => '🌶️',
                'kopi' => '☕', 'minuman' => '🧋', 'makanan' => '🍗'];
        foreach ($map as $key => $icon) {
            if (str_contains(strtolower((string) $name), $key)) return $icon;
        }
        return '🍽️';
    }

    if (! function_exists('maps_embed')) {
        // Peta tertanam untuk alamat
        function maps_embed(?string $address): string
        {
            return 'https://www.google.com/maps?q=' . urlencode((string) $address) . '&output=embed';
        }
    }

    if (! function_exists('maps_search')) {
        // Buka alamat di Google Maps
        function maps_search(?string $address): string
        {
            return 'https://www.google.com/maps/search/?api=1&query=' . urlencode((string) $address);
        }
    }

    if (! function_exists('maps_route')) {
        // Rute ke alamat tujuan (opsional dari alamat asal)
        function maps_route(?string $to, ?string $from = null): string
        {
            $url = 'https://www.google.com/maps/dir/?api=1&travelmode=driving&destination=' . urlencode((string) $to);
            return $from ? $url . '&origin=' . urlencode($from) : $url;
        }
    }
}