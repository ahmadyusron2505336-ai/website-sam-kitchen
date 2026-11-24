<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('merchant_id')->nullable()->after('role')
                  ->constrained('merchants')->nullOnDelete();
        });

        // Hubungkan akun pemilik yang sudah ada ke tokonya
        DB::statement('UPDATE users SET merchant_id = (SELECT id FROM merchants WHERE merchants.user_id = users.id LIMIT 1)');
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('merchant_id'));
    }
};