<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTranslation extends Model
{
    use HasFactory;

    protected $guarded;

    public function delivery() {
        return $this->belongsTo(delivery::class);
    }
}
