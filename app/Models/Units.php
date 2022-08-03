<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class Units extends Model
{

  // protected $guarded = [];
    protected $table = "units";
    protected $fillable = ['name'];

    protected $with = ['unit_translations'];


    public function getTranslation($field = '', $lang = false){
      $lang = $lang == false ? App::getLocale() : $lang;
      $brand_translation = $this->hasMany(UnitsTranslation::class)->where('lang', $lang)->first();
      return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function unit_translations(){
      return $this->hasMany(UnitsTranslation::class , 'unit_id' , 'id');
    }

    

}
