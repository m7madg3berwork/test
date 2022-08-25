<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App;

class UserPackage extends Model
{

    protected $guarded = [];

    protected $table = "user_packages";

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}