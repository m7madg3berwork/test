<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\CustomerResource;
use App\Models\Cart;
use App\Models\ComparisonProducts;
use App\Models\Customer;
use App\Models\Wishlist;

class CustomerController extends Controller
{
    public function show($id)
    {
        return new CustomerResource(Customer::find($id));
    }

    public function getCounts()
    {
        $cart = 0;
        $comparisons = 0;
        $wishlist = 0;

        $cart = Cart::where('user_id', auth()->user()->id)->count();
        $comparisons = ComparisonProducts::where('user_id', auth()->user()->id)->count();
        $wishlist = Wishlist::where('user_id', auth()->user()->id)->count();

        return response()->json([
            'data' => [
                'cart_count' => $cart,
                'comparisons_count' => $comparisons,
                'wishlist_count' => $wishlist,
            ],
            'status' => 200
        ]);
    }
}
