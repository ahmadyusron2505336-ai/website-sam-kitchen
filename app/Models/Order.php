<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public function merchant()   { return $this->belongsTo(Merchant::class); }
    public function items()      { return $this->hasMany(OrderItem::class); }
    public function commission() { return $this->hasOne(Commission::class); }
    public function handler() { return $this->belongsTo(User::class, 'handled_by'); }
    public function customer() { return $this->belongsTo(User::class, 'user_id'); }
    public function reviews()  { return $this->hasMany(Review::class); }

    public function getItemsSummaryAttribute(): string
    {
        return $this->items->map(fn ($i) => $i->product_name
            . ($i->variant ? " {$i->variant}" : '') . " (x{$i->qty})")->implode(', ');
    }

    public const PAYMENT_METHODS = [
        'transfer' => 'Transfer Bank',
        'qris'     => 'QRIS',
        'cod'      => 'COD (Bayar di Tempat)',
    ];

    // Setiap pesanan baru otomatis mendapat token untuk tautan pelacakan
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->token ??= \Illuminate\Support\Str::random(40);
        });
    }

    public const STATUS_LABELS = [
        'menunggu' => 'Menunggu konfirmasi',
        'diproses' => 'Diterima, sedang diproses',
        'selesai'  => 'Selesai',
        'ditolak'  => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
    ];

    public const STATUS_HINTS = [
        'menunggu' => 'Pesanan Anda sudah masuk dan menunggu konfirmasi dari penjual.',
        'diproses' => 'Pesanan diterima penjual dan sedang disiapkan.',
        'selesai'  => 'Pesanan sudah selesai. Terima kasih! Jangan lupa beri rating dan ulasan.',
        'ditolak'  => 'Maaf, pesanan ini ditolak oleh penjual.',
        'dibatalkan' => 'Pesanan ini dibatalkan oleh pelanggan.',
    ];

    // Alamat lengkap untuk ditampilkan (daftar, detail, nota)
    public function getFullAddressAttribute(): string
    {
        $parts = [$this->address];

        if ($this->rt || $this->rw) {
            $parts[] = 'RT ' . ($this->rt ?: '-') . '/RW ' . ($this->rw ?: '-');
        }
        if ($this->kelurahan)   $parts[] = 'Kel. ' . $this->kelurahan;
        if ($this->kecamatan)   $parts[] = 'Kec. ' . $this->kecamatan;
        if ($this->city)        $parts[] = $this->city;
        if ($this->postal_code) $parts[] = $this->postal_code;

        return implode(', ', array_filter($parts));
    }

    // Kata kunci pencarian Google Maps (RT/RW dibuang agar pencarian lebih akurat)
    public function getMapQueryAttribute(): string
    {
        return implode(', ', array_filter([
            $this->address, $this->kelurahan, $this->kecamatan, $this->city, $this->postal_code,
        ]));
    }

    // Tujuan pengantaran: koordinat lama jika ada, selain itu pencarian dari alamat
    public function getDestinationAttribute(): ?string
    {
        if ($this->latitude !== null && $this->longitude !== null) {
            return sprintf('%.7F,%.7F', $this->latitude, $this->longitude);
        }

        return $this->map_query ?: $this->address;
    }

    // Pelanggan hanya boleh membatalkan selama penjual belum menerima pesanan
    public function getCanBeCancelledAttribute(): bool
    {
        return $this->status === 'menunggu';
    }
}