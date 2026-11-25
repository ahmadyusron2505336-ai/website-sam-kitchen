<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    protected $guarded = [];
    protected $casts = ['opening_hours' => 'array'];

    public function user()       { return $this->belongsTo(User::class); }
    public function categories() { return $this->hasMany(Category::class); }
    public function products()   { return $this->hasMany(Product::class); }
    public function orders()     { return $this->hasMany(Order::class); }
    public function commissions(){ return $this->hasMany(Commission::class); }
    // Toko dianggap buka jika belum ada data toko atau kolom bernilai true
    public static function isStoreOpen(): bool
    {
        return (bool) (static::query()->value('is_open') ?? true);
    }
}