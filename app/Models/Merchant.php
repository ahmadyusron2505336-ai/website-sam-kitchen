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
}