<?php

namespace App\Models;

use App;

use Illuminate\Database\Eloquent\Model;

class WeekDays extends Model
{
    protected $table = 'week_days';
    protected $fillable = ['code', 'name', 'name_mobile' , 'active','order_count_customer','order_count_wholesale'];

    // protected $with = ['week_day_translations'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $brand_translation = $this->hasMany(WeekDaysTranslation::class)->where('locale', $lang)->first();
        return $brand_translation != null ? $brand_translation->$field : $this->$field;
    }

    public function week_day_translations()
    {
        return $this->hasMany(WeekDaysTranslation::class, 'week_day_id', 'id');
    }

}
