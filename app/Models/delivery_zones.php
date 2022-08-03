<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class delivery_zones extends Model
{
    use HasFactory;

    protected $fillable = ['delivery_id','cost','zone_id','type'];
}
