<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function own(Request $r, Product $p): void
    {
        abort_if($p->merchant_id !== $r->user()->merchant->id, 403);
    }

    private function rules(Request $request): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'category_id' => ['nullable', Rule::exists('categories', 'id')
                                ->where('merchant_id', $request->user()->merchant->id)],
            'description' => ['nullable', 'string'],
            'price'       => ['nullable', 'integer', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
            'image'       => ['nullable', 'image', 'max:2048'],
            'variants'         => ['nullable', 'array'],
            'variants.*.name'  => ['required', 'string', 'max:50'],
            'variants.*.price' => ['required', 'integer', 'min:0'],
            'addons'           => ['nullable', 'array'],
            'addons.*.name'    => ['required', 'string', 'max:50'],
            'addons.*.price'   => ['nullable', 'integer', 'min:0'],
        ];
    }

    // Harga produk: varian termurah jika ada varian, selain itu dari kolom harga
    private function resolvePrice(array $data): ?int
    {
        $variantPrices = collect($data['variants'] ?? [])->pluck('price')->map(fn ($p) => (int) $p);

        if ($variantPrices->isNotEmpty()) {
            return $variantPrices->min();
        }

        return filled($data['price'] ?? null) ? (int) $data['price'] : null;
    }

    private function payload(array $data, int $price): array
    {
        $payload = collect($data)->only(['name', 'category_id', 'description', 'stock'])->all();
        $payload['price']        = $price;
        $payload['is_available'] = (int) $data['stock'] > 0;   // otomatis dari stok
        return $payload;
    }

    public function index(Request $request)
    {
        $merchant = $request->user()->merchant;

        $products = $merchant->products()->with(['category', 'variants'])
            ->when($request->q, fn ($q, $s) => $q->where('name', 'like', "%$s%"))
            ->when($request->category, fn ($q, $c) => $q->where('category_id', $c))
            ->when($request->stock, function ($q, $s) {
                match ($s) {
                    'tersedia' => $q->where('stock', '>', 10),
                    'menipis'  => $q->whereBetween('stock', [1, 10]),
                    'habis'    => $q->where('stock', 0),
                    default    => null,
                };
            })
            ->latest()->paginate(10)->withQueryString();

        return view('merchant.products.index', [
            'products'   => $products,
            'categories' => $merchant->categories,
        ]);
    }

    public function create(Request $request)
    {
        return view('merchant.products.form', [
            'product'    => new Product(),
            'categories' => $request->user()->merchant->categories,
        ]);
    }

    public function store(Request $request)
    {
        $data  = $request->validate($this->rules($request));
        $price = $this->resolvePrice($data);

        if ($price === null) {
            return back()->withErrors(['price' => 'Isi harga produk, atau tambahkan minimal satu varian berharga.'])->withInput();
        }

        $merchant = $request->user()->merchant;

        DB::transaction(function () use ($request, $data, $price, $merchant) {
            $payload = $this->payload($data, $price);
            if ($request->hasFile('image')) {
                $payload['image'] = $request->file('image')->store('products', 'public');
            }
            $product = $merchant->products()->create($payload);
            $this->syncOptions($product, $data);
        });

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Request $request, Product $product)
    {
        $this->own($request, $product);
        $product->load(['variants', 'addons']);

        return view('merchant.products.form', [
            'product'    => $product,
            'categories' => $request->user()->merchant->categories,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->own($request, $product);
        $data  = $request->validate($this->rules($request));
        $price = $this->resolvePrice($data);

        if ($price === null) {
            return back()->withErrors(['price' => 'Isi harga produk, atau tambahkan minimal satu varian berharga.'])->withInput();
        }

        DB::transaction(function () use ($request, $data, $price, $product) {
            $payload = $this->payload($data, $price);

            if ($request->hasFile('image')) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $payload['image'] = $request->file('image')->store('products', 'public');
            } elseif ($request->boolean('remove_image')) {
                if ($product->image) Storage::disk('public')->delete($product->image);
                $payload['image'] = null;
            }

            $product->update($payload);
            $product->variants()->delete();
            $product->addons()->delete();
            $this->syncOptions($product, $data);
        });

        return redirect()->route('merchant.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, Product $product)
    {
        $this->own($request, $product);
        if ($product->image) Storage::disk('public')->delete($product->image);
        $product->delete();

        return back()->with('success', 'Produk dihapus.');
    }

    private function syncOptions(Product $product, array $data): void
    {
        foreach ($data['variants'] ?? [] as $v) {
            if (filled($v['name'])) {
                $product->variants()->create(['name' => $v['name'], 'price' => (int) $v['price']]);
            }
        }
        foreach ($data['addons'] ?? [] as $a) {
            if (filled($a['name'])) {
                $product->addons()->create(['name' => $a['name'], 'price' => $a['price'] ?? 0]);
            }
        }
    }
}