<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App;

class Zone extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded;
    protected $appends = ['name_city'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $zone_translation = $this->hasMany(ZoneTranslation::class)->where('lang', $lang)->first();
        return $zone_translation != null ? $zone_translation->$field : $this->$field;
    }

    public function zone_translations()
    {
        return $this->hasMany(ZoneTranslation::class);
    }

    public function getNameCityAttribute()
    {
        return $this->name . ' : ' . $this->city()->first()->name;
    }

    public function get()
    {
        return $this->hasMany(ZoneTranslation::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function products()
    {
        return $this->belongsToMany(ProductsZones::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($zone) { // before delete() method call this
            $zone->zone_translations()->delete();
            // do the rest of the cleanup...
        });
    }
}
