<?php

namespace App\Http\Controllers\Customer;

use App\Support\OrderHistory;
use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem};
use App\Support\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    // Metode bayar; Transfer aktif jika rekening diisi, QRIS aktif jika gambar QRIS diunggah
    private function methods($store): array
    {
        return [
            'transfer' => [
                'label' => 'Transfer Bank', 'icon' => 'fa-building-columns',
                'desc'  => 'Transfer ke rekening toko, lalu kirim bukti via WhatsApp.',
                'enabled' => filled($store->bank_account_number),
            ],
            'qris' => [
                'label' => 'QRIS', 'icon' => 'fa-qrcode',
                'desc'  => 'Scan kode QR dengan e-wallet atau mobile banking.',
                'enabled' => filled($store->qris_image),
            ],
            'cod' => [
                'label' => 'COD (Bayar di Tempat)', 'icon' => 'fa-hand-holding-dollar',
                'desc'  => 'Bayar tunai saat pesanan diterima.',
                'enabled' => true,
            ],
        ];
    }

    public function create(Request $request)
    {
        $lines = Cart::lines();
        if (! $lines) {
            return redirect()->route('customer.cart')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        return view('customer.checkout', [
            'lines'   => $lines,
            'total'   => collect($lines)->sum('subtotal'),
            'methods' => $this->methods(current_store()),
        ]);
    }

    public function store(Request $request)
    {
        $store   = current_store();
        $methods = $this->methods($store);

        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:255'],
            'rt'             => ['required', 'digits_between:1,3'],
            'rw'             => ['required', 'digits_between:1,3'],
            'kelurahan'      => ['required', 'string', 'max:100'],
            'kecamatan'      => ['required', 'string', 'max:100'],
            'city'           => ['required', 'string', 'max:100'],
            'postal_code'    => ['required', 'digits:5'],
            'note'           => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', Rule::in(array_keys($methods))],
        ], [
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in'       => 'Metode pembayaran tidak valid.',
            'latitude'       => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],            
            'address.required'        => 'Alamat pengantaran wajib diisi.',
        ]);

        if (! $methods[$data['payment_method']]['enabled']) {
            return back()->withErrors(['payment_method' => 'Metode pembayaran ini belum tersedia.'])->withInput();
        }

        $lines = Cart::lines();
        if (! $lines) {
            return redirect()->route('customer.cart')->withErrors(['cart' => 'Keranjang masih kosong.']);
        }

        // Periksa stok setiap produk (digabung jika varian berbeda)
        foreach (collect($lines)->groupBy(fn ($l) => $l['product']->id) as $group) {
            $p    = $group->first()['product'];
            $need = $group->sum('qty');
            if (! $p->is_available || $p->stock < $need) {
                return redirect()->route('customer.cart')->withErrors([
                    'cart' => "Stok {$p->name} tidak mencukupi (tersisa {$p->stock} porsi).",
                ]);
            }
        }

        $order = DB::transaction(function () use ($data, $lines, $request, $store) {
            do {
                $code = 'SK-' . random_int(10000, 99999);
            } while (Order::where('code', $code)->exists());

            $order = Order::create([
                'code'           => $code,
                'merchant_id'    => $store->id,
                'customer_name'  => $data['customer_name'],
                'address'        => $data['address'],
                'rt'             => $data['rt'],
                'rw'             => $data['rw'],
                'kelurahan'      => $data['kelurahan'],
                'kecamatan'      => $data['kecamatan'],
                'city'           => $data['city'],
                'postal_code'    => $data['postal_code'],                
                'note'           => $data['note'] ?? null,
                'payment_method' => $data['payment_method'],
                'total'          => collect($lines)->sum('subtotal'),
                'status'         => 'menunggu',
            ]);

            foreach ($lines as $l) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $l['product']->id,
                    'product_name' => $l['product']->name,
                    'variant'      => $l['variant']?->name,
                    'addons'       => $l['addons']->pluck('name')->implode(', ') ?: null,
                    'price'        => $l['unit'],
                    'qty'          => $l['qty'],
                    'subtotal'     => $l['subtotal'],
                ]);
            }

            return $order;
        });

        Cart::clear();
        OrderHistory::remember($request, $order->token);

        return redirect()->route('customer.order', $order->token)
            ->with('success', "Pesanan #{$order->code} berhasil dikirim. Simpan tautan halaman ini untuk memantau pesanan Anda.");
    }
}