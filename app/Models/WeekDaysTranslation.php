<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekDaysTranslation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps = false;


    protected $table = "week_day_translations";

  protected $fillable = ['name', 'locale','week_day_id'];

  public function week_day(){
   return $this->belongsTo(WeekDays::class , 'week_day_id' , 'id');
  }


}
