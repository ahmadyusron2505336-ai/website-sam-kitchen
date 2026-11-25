<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\{Category, Order, Product, Review};

class HomeController extends Controller
{
    public function index()
    {
        $store = current_store();
        abort_unless($store, 503, 'Toko belum dikonfigurasi.');

        $categories = Category::where('merchant_id', $store->id)->withCount('products')->get();

        $featured = Product::where('merchant_id', $store->id)
            ->where('is_available', true)
            ->with('category')->withAvg('reviews', 'rating')->withCount(['reviews', 'variants'])
            ->orderByDesc('reviews_count')->latest()->limit(4)->get();

        // Statistik ringkas
        $menuCount = Product::where('merchant_id', $store->id)->count();
        $orderDone = Order::where('merchant_id', $store->id)->where('status', 'selesai')->count();

        $reviewScope   = fn () => Review::whereHas('product', fn ($q) => $q->where('merchant_id', $store->id));
        $avgRating     = $reviewScope()->avg('rating');
        $latestReviews = $reviewScope()->with('product')->whereNotNull('comment')->latest()->limit(3)->get();

        // Status toko: tombol Tutup Toko di admin + jam operasional
        $status         = $store->currentStatus();
        $isOpen         = $status['open'];
        $todayHours     = $status['hours'];

        return view('customer.home', compact(
            'store', 'categories', 'featured', 'menuCount', 'orderDone',
            'avgRating', 'latestReviews', 'isOpen', 'todayHours'
        ));        
    }
}