<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPackageProducts extends Model
{
    use HasFactory;

    protected $guarded;

    public function customer_package() {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
