<?php

namespace App\Http\Middleware;

use App\Models\Merchant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreOpen
{
    // Route yang diblokir saat toko tutup. Samakan dengan hasil: php artisan route:list --name=customer
    private const BLOCKED = [
        'customer.cart.add',
        'customer.cart.update',
        'customer.checkout',
        'customer.checkout.store',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs(...self::BLOCKED) || Merchant::isStoreOpen()) {
            return $next($request);
        }

        $message = 'Maaf, toko sedang tutup. Pesanan baru belum bisa dibuat. Silakan kembali saat toko buka.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 423);
        }

        return redirect()->route('customer.menu')->with('error', $message);
    }
}