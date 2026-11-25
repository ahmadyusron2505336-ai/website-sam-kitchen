<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opening_hours' => 'array',
    ];

    public function user()        { return $this->belongsTo(User::class); }
    public function categories()  { return $this->hasMany(Category::class); }
    public function products()    { return $this->hasMany(Product::class); }
    public function orders()      { return $this->hasMany(Order::class); }
    public function commissions() { return $this->hasMany(Commission::class); }

    // Dipakai middleware dan checkout: false jika di luar jam operasional atau hari libur
    public static function isStoreOpen(): bool
    {
        $merchant = static::query()->first();

        return $merchant ? $merchant->currentStatus()['open'] : true;
    }

    // Status toko saat ini. reason: 'jam' (di luar jam operasional atau hari libur), null (buka)
    public function currentStatus(): array
    {
        $now = now('Asia/Jakarta');
        $day = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'][$now->dayOfWeek];
        $h   = $this->opening_hours[$day] ?? null;

        $dayOpen = $h && ! ($h['closed'] ?? false);
        $hours   = $dayOpen ? $h['open'] . ' - ' . $h['close'] : null;

        // Jadwal belum diatur sama sekali: jangan mengunci toko
        if (empty($this->opening_hours)) {
            return ['open' => true, 'reason' => null, 'hours' => null];
        }

        $inHours = false;

        if ($dayOpen) {
            $time  = $now->format('H:i');
            $open  = $h['open'];
            $close = $h['close'];

            $inHours = $open <= $close
                ? ($time >= $open && $time < $close)      // jadwal biasa, mis. 08:00 - 21:00
                : ($time >= $open || $time < $close);     // melewati tengah malam, mis. 18:00 - 02:00
        }

        return ['open' => $inHours, 'reason' => $inHours ? null : 'jam', 'hours' => $hours];
    }
}