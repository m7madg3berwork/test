<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class zones extends Model
{

    protected $with = ['zones_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $zones_translation = $this->zones_translations->where('lang', $lang)->first();
        return $zones_translation != null ? $zones_translation->$field : $this->$field;
    }

    public function zones_translations()
    {
        return $this->hasMany(zonesTranslation::class, 'zone_id');
    }

    public function delivery() {
        return $this->belongsToMany(zones::class, 'delivery_zones');
    }


}
