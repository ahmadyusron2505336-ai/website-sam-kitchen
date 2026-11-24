<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class OrderHistory
{
    const COOKIE = 'my_orders';

    /** Token pesanan yang tersimpan di browser ini. */
    public static function tokens(Request $request): array
    {
        $raw = json_decode((string) $request->cookie(self::COOKIE, '[]'), true);

        return is_array($raw) ? array_values(array_filter($raw, 'is_string')) : [];
    }

    /** Simpan token (maksimal 20 pesanan terakhir, berlaku 1 tahun). */
    public static function remember(Request $request, string $token): void
    {
        $tokens = collect(self::tokens($request))->prepend($token)->unique()->take(20)->values()->all();

        Cookie::queue(self::COOKIE, json_encode($tokens), 60 * 24 * 365);
    }

    /** Hapus satu pesanan dari riwayat browser ini. */
    public static function forget(Request $request, string $token): void
    {
        $tokens = array_values(array_diff(self::tokens($request), [$token]));

        if ($tokens) {
            Cookie::queue(self::COOKIE, json_encode($tokens), 60 * 24 * 365);
        } else {
            Cookie::queue(Cookie::forget(self::COOKIE));
        }
    }

    /** Hapus seluruh riwayat browser ini. */
    public static function clear(): void
    {
        Cookie::queue(Cookie::forget(self::COOKIE));
    }
}