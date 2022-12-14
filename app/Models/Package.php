<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Package extends Model
{

    protected $table = "packages";

    protected $fillable = [
        'name', 'desc', 'added_by', 'user_id', 'price', 'logo', 'customer_type', 'shipping_type', 'duration', 'visits_num', 'active'
    ];

    protected $with = [
        'package_translations'
    ];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? app()->getLocale() : $lang;
        $brand_translation = $this->hasMany(PackageTranslation::class)->where('lang', $lang)->first();
        return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function package_translations()
    {
        return $this->hasMany(PackageTranslation::class, 'package_id', 'id');
    }

    public function package_items()
    {
        return $this->hasMany(PackageItem::class, 'package_id', 'id');
    }

    public function package_shipping_days()
    {
        return $this->hasMany(PackageShippingDays::class, 'package_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'package_items')->withPivot('qty');
    }

    public function productsSelected()
    {
        return $this->hasMany(PackageItem::class, 'package_id')->withPivot('qty');
    }

    public function user_packages()
    {
        return $this->hasMany(UserPackage::class, 'package_id', 'id');
    }

    public function states()
    {
        return $this->belongsToMany(State::class, 'packages_states');
    }
}