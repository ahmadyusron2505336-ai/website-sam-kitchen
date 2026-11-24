<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('rt', 5)->nullable()->after('address');
            $table->string('rw', 5)->nullable()->after('rt');
            $table->string('kelurahan')->nullable()->after('rw');
            $table->string('kecamatan')->nullable()->after('kelurahan');
            $table->string('city')->nullable()->after('kecamatan');
            $table->string('postal_code', 10)->nullable()->after('city');
        });
    }

    public function down(): void
    {
        Schema::table('orders', fn (Blueprint $table) => $table->dropColumn(
            ['rt', 'rw', 'kelurahan', 'kecamatan', 'city', 'postal_code']
        ));
    }
};