<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnitsTranslation extends Model
{

  protected $table = "unit_translations";

  protected $fillable = ['name', 'lang','unit_id'];

  public function unit(){
   return $this->belongsTo(Units::class);
  }
}
