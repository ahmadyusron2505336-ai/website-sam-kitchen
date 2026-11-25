<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StoreStatusController extends Controller
{
    public function toggle(Request $request)
    {
        $merchant = $request->user()->merchant;
        $merchant->update(['is_open' => ! $merchant->is_open]);

        return back()->with('success', $merchant->is_open
            ? 'Toko dibuka. Pelanggan sudah bisa memesan.'
            : 'Toko ditutup. Pelanggan tidak bisa memesan sampai toko dibuka kembali.');
    }
}