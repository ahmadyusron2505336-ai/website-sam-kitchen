<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $col = DB::selectOne("SHOW COLUMNS FROM orders WHERE Field = 'status'");

        if ($col && str_starts_with(strtolower($col->Type), 'enum(')) {
            preg_match_all("/'([^']+)'/", $col->Type, $m);
            $values = $m[1];

            if (! in_array('dibatalkan', $values, true)) {
                $values[] = 'dibatalkan';
                $list = implode(',', array_map(fn ($v) => "'{$v}'", $values));
                DB::statement("ALTER TABLE orders MODIFY status ENUM({$list}) NOT NULL DEFAULT 'menunggu'");
            }
        }
    }

    public function down(): void
    {
        // Dibiarkan kosong agar data pesanan berstatus dibatalkan tidak rusak.
    }
};