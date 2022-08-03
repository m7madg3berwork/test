<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsZones extends Model
{
    use HasFactory;

    protected $guarded;

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function zone() {
        return $this->belongsTo(Zone::class);
    }
}
