<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('token', 40)->nullable()->after('code');
            $table->text('address')->nullable()->after('customer_phone');
            $table->string('payment_method', 20)->nullable()->after('note');
        });

        // Token untuk pesanan yang sudah ada
        DB::table('orders')->whereNull('token')->orderBy('id')->each(function ($o) {
            DB::table('orders')->where('id', $o->id)->update(['token' => Str::random(40)]);
        });

        Schema::table('orders', fn (Blueprint $table) => $table->unique('token'));

        Schema::table('merchants', function (Blueprint $table) {
            $table->string('qris_image')->nullable()->after('bank_account_name');
        });

        // Ulasan: pemesan tanpa akun, jadi user_id boleh kosong dan nama pemberi ulasan disimpan
        Schema::dropIfExists('reviews');
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reviewer_name', 100);
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('merchants', fn (Blueprint $table) => $table->dropColumn('qris_image'));
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['token']);
            $table->dropColumn(['token', 'address', 'payment_method']);
        });
    }
};