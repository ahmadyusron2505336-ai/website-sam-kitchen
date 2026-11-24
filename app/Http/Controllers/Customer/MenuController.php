<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\{Category, Product};
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $store = current_store();
        $categories = Category::where('merchant_id', $store->id)->get();

        $products = Product::where('merchant_id', $store->id)
            ->with('category')->withAvg('reviews', 'rating')->withCount(['reviews', 'variants'])
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->orderByDesc('is_available')->orderBy('name')->get();

        return view('customer.menu.index', compact('categories', 'products'));
    }

    public function show(Product $product)
    {
        abort_unless((int) $product->merchant_id === (int) current_store()->id, 404);

        $product->load(['category', 'variants', 'addons'])->loadAvg('reviews', 'rating')->loadCount('reviews');
        $reviews = $product->reviews()->with('user')->latest()->limit(10)->get();
        $reviews = $product->reviews()->latest()->limit(10)->get();

        return view('customer.menu.show', compact('product', 'reviews'));
    }
}