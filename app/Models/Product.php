<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];
    protected $casts = ['is_available' => 'boolean', 'stock' => 'integer'];

    public function getStockStatusAttribute(): string
    {
        return match (true) {
            $this->stock <= 0  => 'habis',
            $this->stock <= 10 => 'menipis',
            default            => 'tersedia',
        };
    }
    public function merchant() { return $this->belongsTo(Merchant::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function variants() { return $this->hasMany(Variant::class); }
    public function addons()   { return $this->hasMany(Addon::class); }
    public function reviews() { return $this->hasMany(Review::class); }
}