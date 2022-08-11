<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsStates extends Model
{
    use HasFactory;

    protected $table = "products_states";

    protected $fillable = [
        'product_id',
        'state_id',
        'cost',
        'qty'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}