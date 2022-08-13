<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\AddressCollection;
use App\Http\Resources\V2\PickupPointResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\City;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\State;
use App\Models\Zone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function pickup_list()
    {
        $pickup_point_list = PickupPoint::where('pick_up_status', '=', 1)->get();

        return PickupPointResource::collection($pickup_point_list);
        // return response()->json(['result' => true, 'pickup_points' => $pickup_point_list], 200);
    }

    public function shipping_cost(Request $request, $id)
    {
        // get address
        $address = Address::find($id);

        // get Zone
        $state = State::find($address->state_id);

        $cost = auth()->user()->customer_type == 'wholesale' ? $state->wholesaler_cost : $state->retailer_cost;

        return response()->json(
            [
                'result'        => true,
                'value'         => convert_price($cost),
                'value_string'  => format_price($cost)
            ],
            200
        );
    }
}