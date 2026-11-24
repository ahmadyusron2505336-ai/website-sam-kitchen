<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $guarded = [];
    public function order() { return $this->belongsTo(Order::class); }
}
