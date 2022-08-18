<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class PackageItem extends Model
{

    // protected $guarded = [];
    protected $table = "package_items";
    protected $fillable = ['package_id', 'product_id', 'qty'];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}