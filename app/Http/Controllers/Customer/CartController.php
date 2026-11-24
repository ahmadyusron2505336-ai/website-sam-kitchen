<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $lines = Cart::lines();

        return view('customer.cart', ['lines' => $lines, 'total' => collect($lines)->sum('subtotal')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer'],
            'qty'        => ['required', 'array'],
            'qty.*'      => ['nullable', 'integer', 'min:0', 'max:99'],
            'addons'     => ['nullable', 'array'],
            'addons.*'   => ['integer'],
        ]);

        $product = Product::with('variants')
            ->where('merchant_id', current_store()->id)->findOrFail($data['product_id']);

        if ($product->stock <= 0 || ! $product->is_available) {
            return back()->withErrors(['qty' => 'Maaf, produk ini sedang habis.']);
        }

        // Hanya add-on milik produk ini yang diterima
        $addonIds = $product->addons()->whereIn('id', $data['addons'] ?? [])->pluck('id')->all();
        $qty      = collect($data['qty'])->map(fn ($q) => (int) $q);

        // Pilihan: [id varian => jumlah]; produk tanpa varian memakai kunci 0
        $picked = [];
        if ($product->variants->isNotEmpty()) {
            foreach ($product->variants as $v) {
                if (($qty[$v->id] ?? 0) > 0) {
                    $picked[] = [$v->id, $qty[$v->id]];
                }
            }
        } elseif (($qty[0] ?? 0) > 0) {
            $picked[] = [null, $qty[0]];
        }

        if (! $picked) {
            return back()->withErrors(['qty' => 'Pilih minimal satu varian dan isi jumlahnya.'])->withInput();
        }

        foreach ($picked as [$variantId, $q]) {
            Cart::add($product->id, $variantId, $addonIds, $q);
        }

        return redirect()->route('customer.cart')->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, string $key)
    {
        $request->validate(['qty' => ['required', 'integer', 'min:0', 'max:99']]);
        Cart::update($key, (int) $request->qty);

        return back();
    }

    public function destroy(string $key)
    {
        Cart::remove($key);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }
}