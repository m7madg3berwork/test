<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageTranslation extends Model
{

  protected $table = "package_translations";

  protected $fillable = ['name', 'desc' , 'lang','package_id'];

  public function package(){
   return $this->belongsTo(Package::class);
  }
}
