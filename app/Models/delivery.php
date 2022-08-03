<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App;

class delivery extends Model
{
    use HasFactory;

    protected $guarded;

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? App::getLocale() : $lang;
        $delivery_translation = $this->hasMany(DeliveryTranslation::class)->where('lang', $lang)->first();
        return $delivery_translation != null ? $delivery_translation->$field : $this->$field;
    }

    public function delivery_translations(){
        return $this->hasMany(DeliveryTranslation::class);
    }

    public function zones() {
        return $this->belongsToMany(zones::class, 'delivery_zones', 'delivery_id','zone_id');
    }

}
