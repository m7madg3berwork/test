<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class PackageShippingDays extends Model
{

  // protected $guarded = [];
    protected $table = "package_shipping_days";
    protected $fillable = ['package_id' , 'date'];

    protected $hidden = ['week_day'];

    public function package(){
      return $this->belongsTo(Package::class , 'package_id' , 'id');
    }


    public function week_day(){
      return $this->belongsTo(WeekDays::class , 'date' , 'id');
     }



}
