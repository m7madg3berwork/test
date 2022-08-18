<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class PackagesStates extends Model
{
    protected $table = "packages_states";

    protected $fillable = ['package_id', 'state_id'];


    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }
}