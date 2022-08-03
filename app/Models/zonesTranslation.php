<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class zonesTranslation extends Model
{
  protected $fillable = ['name', 'lang', 'zones_id'];

  protected $table = "zone_translations";

  public function brand(){
    return $this->belongsTo(zones::class);
  }
}
