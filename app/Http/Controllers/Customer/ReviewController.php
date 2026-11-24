<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\{Order, Review};
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, string $token)
    {
        $order = Order::where('token', $token)->firstOrFail();

        abort_unless($order->status === 'selesai', 403, 'Ulasan hanya bisa diberikan setelah pesanan selesai.');

        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'rating'     => ['required', 'integer', 'between:1,5'],
            'comment'    => ['nullable', 'string', 'max:500'],
        ], ['rating.required' => 'Pilih rating bintang terlebih dahulu.']);

        // Produk harus benar-benar ada di pesanan ini
        abort_unless($order->items()->where('product_id', $data['product_id'])->exists(), 403);

        Review::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $data['product_id']],
            [
                'reviewer_name' => $order->customer_name,
                'rating'        => $data['rating'],
                'comment'       => $data['comment'] ?? null,
            ]
        );

        return back()->with('success', 'Terima kasih! Ulasan Anda berhasil dikirim.');
    }
}