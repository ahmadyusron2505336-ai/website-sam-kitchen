<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('merchant_id')
                  ->constrained()->nullOnDelete();
            $table->text('note')->nullable()->after('customer_phone');
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'product_id']); // 1 ulasan per produk per pesanan
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn('note');
        });
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('phone'));
    }
};