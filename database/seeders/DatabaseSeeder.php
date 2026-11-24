<?php

namespace Database\Seeders;

use App\Models\{User, Merchant, Category, Product, Order, OrderItem, Commission, Review};
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Sam', 'email' => 'adminsamkitchen@gmail.com',
            // teks biasa sesuai pengaturan login tanpa hash;
            // jika hashing diaktifkan lagi, ganti dengan Hash::make('daffayono')
            'password' => 'daffayono', 'role' => 'pengelola',
        ]);
        // Hanya contoh "penanggung jawab" lain pada laporan
        $budi = User::create([
            'name' => 'Admin Budi', 'email' => 'adminbudi@samkitchen.test',
            'password' => 'password', 'role' => 'pengelola',
        ]);

        // Ulasan contoh (tanpa akun, hanya nama pemberi ulasan)
        $reviewers = ['Budi Santoso', 'Siti Rahma', 'Andi Pratama'];
        $comments  = ['Enak banget, porsinya pas!', 'Rasanya mantap, pasti pesan lagi.',
                      'Masih hangat saat sampai.', 'Sambalnya pedas nikmat.', 'Harga sesuai kualitas.'];
        $ratings   = [5, 5, 5, 4, 4, 3];
        $owner = [];
        $used  = [];

        OrderItem::whereHas('order', fn ($q) => $q->where('status', 'selesai')
                ->where('created_at', '<', now()->startOfDay()))
            ->inRandomOrder()->limit(40)->get()
            ->each(function ($item) use ($reviewers, $comments, $ratings, &$owner, &$used) {
                $pair = $item->order_id . '-' . $item->product_id;
                if (isset($used[$pair])) return;
                $used[$pair] = true;

                $name = $owner[$item->order_id] ??= $reviewers[array_rand($reviewers)];
                Order::whereKey($item->order_id)->update(['customer_name' => $name]);

                Review::create([
                    'product_id'    => $item->product_id,
                    'order_id'      => $item->order_id,
                    'reviewer_name' => $name,
                    'rating'        => $ratings[array_rand($ratings)],
                    'comment'       => $comments[array_rand($comments)],
                ]);
            });
            
        $m = Merchant::create([
            'user_id' => $admin->id,
            'name' => 'SAM KITCHEN - Delicious Food & Drink',
            'address' => 'Jl. Soekarno Hatta No. 10, Lowokwaru, Malang, Jawa Timur',
            'whatsapp' => '081234567890',
            'business_email' => 'officialsam.1807@gmail.com',
            'bank_name' => 'BCA', 'bank_account_number' => '1234567890', 'bank_account_name' => 'Admin Sam',
            'opening_hours' => collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'])
                ->mapWithKeys(fn ($d) => [$d => ['open' => '08:00', 'close' => '21:00', 'closed' => false]])
                ->all(),
        ]);

        $admin->update(['merchant_id' => $m->id]);
        $budi->update(['merchant_id' => $m->id]);

        $cat = collect(['Dimsum', 'Snack', 'Sambal', 'Minuman', 'Makanan Utama'])
            ->mapWithKeys(fn ($n) => [$n => Category::create(['merchant_id' => $m->id, 'name' => $n])]);

        $products = collect([
            ['Dimsum Mentai', 'Dimsum', 25000, 45],
            ['Kripik Pisang Special', 'Snack', 15000, 80],
            ['Sambal Bajak Kemasan', 'Sambal', 20000, 12],
            ['Kopi Susu Gula Aren', 'Minuman', 18000, 60],
            ['Bebek Goreng Sambal Ijo', 'Makanan Utama', 32000, 8],
            ['Es Teh Manis Jumbo', 'Minuman', 7000, 100],
        ])->map(fn ($p) => Product::create([
            'merchant_id' => $m->id, 'category_id' => $cat[$p[1]]->id,
            'name' => $p[0], 'price' => $p[2], 'stock' => $p[3], 'is_available' => $p[3] > 0,
        ]));

        // Contoh varian isi untuk Dimsum Mentai (harga terendah sama dengan harga produk)
        foreach ([['Isi 4', 25000], ['Isi 6', 35000], ['Isi 8', 45000]] as [$nama, $harga]) {
            $products[0]->variants()->create(['name' => $nama, 'price' => $harga]);
        }

        // Pembuat pesanan: $lines = [[indeks produk, qty], ...]
        $make = function (string $code, string $name, string $status, array $lines, $date)
            use ($m, $products, $admin, $budi) {

            $handler = $status === 'menunggu' ? null : (rand(0, 1) && $status === 'selesai' ? $budi->id : $admin->id);

            $order = Order::create([
                'code' => $code, 'merchant_id' => $m->id, 'customer_name' => $name,
                'customer_phone' => '08' . rand(1000000000, 1999999999),
                'total' => 0, 'status' => $status, 'handled_by' => $handler,
                'created_at' => $date, 'updated_at' => $date,
            ]);

            $total = 0;
            foreach ($lines as [$i, $qty]) {
                $p = $products[$i];
                $total += $sub = $p->price * $qty;
                OrderItem::create([
                    'order_id' => $order->id, 'product_id' => $p->id, 'product_name' => $p->name,
                    'price' => $p->price, 'qty' => $qty, 'subtotal' => $sub,
                ]);
            }
            $order->update(['total' => $total]);

            if ($status !== 'menunggu') {
                Commission::create([
                    'merchant_id' => $m->id, 'order_id' => $order->id, 'order_total' => $total,
                    'rate' => 10, 'amount' => (int) round($total * 0.10),
                ]);
            }
        };

        // Riwayat 29 hari ke belakang (untuk grafik dan laporan)
        $names = ['Budi Santoso', 'Siti Rahma', 'Andi Pratama', 'Dewi Lestari', 'Rizky Maulana', 'Nina Safitri'];
        $n = 8000;
        for ($d = 29; $d >= 1; $d--) {
            $jumlah = rand(3, 6);
            for ($k = 0; $k < $jumlah; $k++) {
                $date = now()->subDays($d)->setTime(rand(9, 20), rand(0, 59));
                $make('SK-' . (++$n), $names[array_rand($names)], 'selesai',
                    [[rand(0, 5), rand(1, 3)], [rand(0, 5), rand(1, 2)]], $date);
            }
        }

        // Pesanan hari ini (sama seperti mockup)
        $make('SK-9016', 'Daftapunk', 'selesai', [[3, 3]], now()->subMinutes(60));
        $make('SK-9017', 'Albondet', 'diproses', [[0, 1]], now()->subMinutes(45));
        $make('SK-9018', 'Yutimah', 'menunggu', [[2, 2]], now()->subMinutes(30));
        $make('SK-9019', 'Suparminto', 'selesai', [[3, 2]], now()->subMinutes(20));
        $make('SK-9020', 'Ridha Syamsudin', 'diproses', [[1, 3]], now()->subMinutes(10));
        $make('SK-9021', 'Arif Wicaksono', 'menunggu', [[0, 2], [3, 1]], now()->subMinutes(5));
    }
}