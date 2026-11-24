<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->unsignedInteger('price')->default(0)->after('name');
        });

        // Varian lama: harga varian = harga produk + tambahan harga
        DB::statement('UPDATE variants v JOIN products p ON p.id = v.product_id
                       SET v.price = GREATEST(CAST(p.price AS SIGNED) + v.extra_price, 0)');

        // Produk yang punya varian: harga produk = harga varian termurah
        DB::statement('UPDATE products p
                       SET p.price = (SELECT MIN(v.price) FROM variants v WHERE v.product_id = p.id)
                       WHERE EXISTS (SELECT 1 FROM variants v WHERE v.product_id = p.id)');

        Schema::table('variants', fn (Blueprint $table) => $table->dropColumn('extra_price'));
    }

    public function down(): void
    {
        Schema::table('variants', fn (Blueprint $table) => $table->integer('extra_price')->default(0)->after('name'));

        DB::statement('UPDATE variants v JOIN products p ON p.id = v.product_id
                       SET v.extra_price = CAST(v.price AS SIGNED) - CAST(p.price AS SIGNED)');

        Schema::table('variants', fn (Blueprint $table) => $table->dropColumn('price'));
    }
};