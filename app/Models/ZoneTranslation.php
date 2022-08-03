<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZoneTranslation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'lang', 'zone_id'];

    public function zone(){
        return $this->belongsTo(Zone::class);
    }

}
