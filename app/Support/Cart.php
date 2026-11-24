<?php

namespace App\Support;

use App\Models\Product;

class Cart
{
    const KEY = 'cart';

    public static function raw(): array
    {
        return session(self::KEY, []);
    }

    public static function add(int $productId, ?int $variantId, array $addonIds, int $qty): void
    {
        $addonIds = collect($addonIds)->map(fn ($i) => (int) $i)->unique()->sort()->values()->all();
        $key = md5($productId . '|' . ($variantId ?? 0) . '|' . implode(',', $addonIds));

        $cart = self::raw();
        $cart[$key] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'addon_ids'  => $addonIds,
            'qty'        => min(99, ($cart[$key]['qty'] ?? 0) + $qty),
        ];
        session([self::KEY => $cart]);
    }

    public static function update(string $key, int $qty): void
    {
        $cart = self::raw();
        if (! isset($cart[$key])) return;

        if ($qty <= 0) unset($cart[$key]);
        else $cart[$key]['qty'] = min(99, $qty);

        session([self::KEY => $cart]);
    }

    public static function remove(string $key): void
    {
        $cart = self::raw();
        unset($cart[$key]);
        session([self::KEY => $cart]);
    }

    public static function clear(): void
    {
        session()->forget(self::KEY);
    }

    public static function count(): int
    {
        return (int) collect(self::raw())->sum('qty');
    }

    /** Baris keranjang lengkap, harga diambil dari database. */
    public static function lines(): array
    {
        $cart = self::raw();
        if (! $cart) return [];

        $products = Product::with(['variants', 'addons'])
            ->whereIn('id', collect($cart)->pluck('product_id')->unique())
            ->get()->keyBy('id');

        $lines = [];
        foreach ($cart as $key => $row) {
            $p = $products[$row['product_id']] ?? null;
            if (! $p) continue;

            $variant = $row['variant_id'] ? $p->variants->firstWhere('id', $row['variant_id']) : null;
            $addons  = $p->addons->whereIn('id', $row['addon_ids']);
            $base = $variant ? $variant->price : $p->price;
            $unit = $base + $addons->sum('price');
            
            $lines[] = [
                'key' => $key, 'product' => $p, 'variant' => $variant, 'addons' => $addons,
                'qty' => $row['qty'], 'unit' => $unit, 'subtotal' => $unit * $row['qty'],
            ];
        }
        return $lines;
    }
}