<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIsPengelola
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'pengelola' || ! $user->merchant) {
            abort(403, 'Hanya pengelola mitra yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}