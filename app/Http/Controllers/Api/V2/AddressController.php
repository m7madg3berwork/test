<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\City;
use App\Models\Country;
use App\Http\Resources\V2\AddressCollection;
use App\Models\Address;
use App\Http\Resources\V2\CitiesCollection;
use App\Http\Resources\V2\StatesCollection;
use App\Http\Resources\V2\CountriesCollection;
use App\Http\Resources\V2\ZonesCollection;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use App\Models\State;
use App\Models\Zone;

class AddressController extends Controller
{
    public function addresses()
    {
        return new AddressCollection(Address::where('user_id', auth()->user()->id)->get());
    }

    public function createShippingAddress(Request $request)
    {
        $address = new Address;
        $address->user_id     = auth()->user()->id;
        $address->address     = $request->address;
        $address->country_id  = $request->country_id;
        $address->state_id    = $request->state_id;
        // $address->city_id     = $request->city_id;
        // $address->zone_id     = $request->zone_id;
        $address->postal_code = $request->postal_code;
        $address->phone       = $request->phone;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been added successfully'),
            'data' =>
            [
                'id'                 => (int) $address->id,
                'user_id'            => (int) $address->user_id,
                'address'            => $address->address,
                'country_id'         => (int)  $address->country_id,
                'state_id'           => (int) $address->state_id,
                // 'city_id'            => (int) $address->city_id,
                // 'zone_id'            => (int) $address->zone_id,
                'country_name'       => optional($address->country)->name,
                'state_name'         => optional($address->state)->name,
                // 'city_name'          => optional($address->city)->name,
                // 'zone_name'          => optional($address->zone)->name,
                'postal_code'        => $address->postal_code,
                'phone'              => $address->phone,
            ]
        ]);
    }

    public function updateShippingAddress(Request $request)
    {
        $address = Address::find($request->id);
        $address->address = $request->address;
        $address->country_id = $request->country_id;
        $address->state_id = $request->state_id;
        // $address->city_id = $request->city_id;
        // $address->zone_id = $request->zone_id;
        $address->postal_code = $request->postal_code;
        $address->phone = $request->phone;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been updated successfully')
        ]);
    }

    public function updateShippingAddressLocation(Request $request)
    {
        $address = Address::find($request->id);
        $address->latitude = $request->latitude;
        $address->longitude = $request->longitude;
        $address->save();

        return response()->json([
            'result' => true,
            'message' => translate('Shipping location in map updated successfully')
        ]);
    }


    public function deleteShippingAddress($id)
    {
        $address = Address::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if ($address == null) {
            return response()->json([
                'result' => false,
                'message' => translate('Address not found')
            ]);
        }
        $address->delete();
        return response()->json([
            'result' => true,
            'message' => translate('Shipping information has been deleted')
        ]);
    }

    public function makeShippingAddressDefault(Request $request)
    {
        try {
            //make all user addressed non default first
            Address::where('user_id', auth()->user()->id)
                ->update(
                    [
                        'set_default' => 0
                    ]
                );

            $address = Address::findOrFail($request->address_id);
            $address->set_default = 1;
            $address->save();

            return response()->json([
                'result' => true,
                'message' => translate('Default shipping information has been updated')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    public function updateAddressInCart(Request $request)
    {
        try {
            Cart::where('user_id', auth()->user()->id)
                ->update(
                    [
                        'address_id' => $request->address_id
                    ]
                );

            $carts = Cart::where("user_id", auth()->user()->id)->get();

            $arr = [];
            foreach ($carts as $cart) {
                $product = Product::findOrFail($cart->product_id);
                $address = Address::findOrFail($request->address_id);
                $state = State::find($address->state_id);
                $stateProduct = $product->states->where("state_id", $state->id)->first();
                Cart::where('user_id', auth()->user()->id)
                    ->where('product_id', $product->id)
                    ->update(
                        [
                            'price' => $stateProduct->cost,
                            'shipping_cost' => auth()->user()->customer_type == 'wholesale' ? $state->wholesaler_cost : $state->retailer_cost
                        ]
                    );
                if ($stateProduct->qty < $cart->quantity) {
                    $arr[] = 1;
                }
            }

            if (count($arr) > 0) {
                return response()->json(
                    [
                        'result' => false,
                        'message' => translate('Address is updated but some products not available please process again.')
                    ]
                );
            }

            return response()->json(
                [
                    'result' => true,
                    'message' => translate('Address is updated')
                ]
            );
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => translate('Could not save the address')
            ]);
        }
    }

    public function getCities()
    {
        return new CitiesCollection(City::where('status', 1)->get());
    }

    public function getZones()
    {
        $zones = Zone::all();
        if (request()->has('city_id')) {
            $zones = Zone::where("city_id", request('city_id'))
                ->get();
        }
        return new ZonesCollection($zones);
    }

    public function getStates()
    {
        return new StatesCollection(State::where('status', 1)->get());
    }

    public function getCountries(Request $request)
    {
        $country_query = Country::where('status', 1);
        if ($request->name != "" || $request->name != null) {
            $country_query->where('name', 'like', '%' . $request->name . '%');
        }
        $countries = $country_query->get();

        return new CountriesCollection($countries);
    }

    public function getCitiesByState($state_id, Request $request)
    {
        $city_query = City::where('status', 1)->where('state_id', $state_id);
        if ($request->name != "" || $request->name != null) {
            $city_query->where('name', 'like', '%' . $request->name . '%');
        }
        $cities = $city_query->get();
        return new CitiesCollection($cities);
    }

    public function getStatesByCountry($country_id, Request $request)
    {
        $state_query = State::where('status', 1)->where('country_id', $country_id);
        if ($request->name != "" || $request->name != null) {
            $state_query->where('name', 'like', '%' . $request->name . '%');
        }
        $states = $state_query->get();
        return new StatesCollection($states);
    }
}