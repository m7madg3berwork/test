<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class CommercialPhotos extends Model
{

  // protected $guarded = [];
  protected $table = "commercial_photos";
  protected $fillable = ['image', 'user_id', 'type'];



  public function user()
  {
    return $this->belongsTo(User::class , 'user_id', 'id');
  }

  
}
