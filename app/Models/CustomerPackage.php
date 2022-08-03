<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class CustomerPackage extends Model
{
    protected $guarded;

    protected $appends = ['product_ids'];

    public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? App::getLocale() : $lang;
      $brand_translation = $this->hasMany(CustomerPackageTranslation::class)->where('lang', $lang)->first();
      return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function customer_package_translations(){
      return $this->hasMany(CustomerPackageTranslation::class);
    }

    public function customer_package_payments()
    {
        return $this->hasMany(CustomerPackagePayment::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class, 'customer_package_products');
    }

    public function getProductIdsAttribute()
    {
        return $this->products()->pluck('product_id');
    }

}
