<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->after('price');
        });

        Schema::table('merchants', function (Blueprint $table) {
            $table->string('business_email')->nullable()->after('whatsapp');
            $table->json('opening_hours')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('handled_by')->nullable()->after('status')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $t) => $t->dropConstrainedForeignId('handled_by'));
        Schema::table('merchants', fn (Blueprint $t) => $t->dropColumn(['business_email', 'opening_hours']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('stock'));
    }
};