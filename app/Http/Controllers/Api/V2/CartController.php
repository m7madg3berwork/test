<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Shop;
use App\Models\State;
use App\Models\User;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{

    use GeneralTrait;

    public function summary()
    {
        try {
            $items = auth()->user()->carts;
            if ($items->isEmpty()) {
                return response()->json([
                    'sub_total' => format_price(0.00),
                    'tax' => format_price(0.00),
                    'shipping_cost' => format_price(0.00),
                    'discount' => format_price(0.00),
                    'grand_total' => format_price(0.00),
                    'grand_total_value' => 0.00,
                    'coupon_code' => "",
                    'coupon_applied' => false,
                ]);
            }

            $sum = 0.00;
            $subtotal = 0.00;
            $tax = 0.00;
            foreach ($items as $cartItem) {
                $item_sum = 0.00;
                $item_sum += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
                $item_sum += $cartItem->shipping_cost - $cartItem->discount;
                $sum +=  $item_sum;   //// 'grand_total' => $request->g

                $subtotal += $cartItem->price * $cartItem->quantity;
                $tax += $cartItem->tax * $cartItem->quantity;
            }

            return response()->json([
                'sub_total' => format_price($subtotal),
                'tax' => format_price($tax),
                'shipping_cost' => format_price($items->sum('shipping_cost')),
                'discount' => format_price($items->sum('discount')),
                'grand_total' => format_price($sum),
                'grand_total_value' => convert_price($sum),
                'coupon_code' => $items[0]->coupon_code,
                'coupon_applied' => $items[0]->coupon_applied == 1,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    public function getList()
    {
        $owner_ids = Cart::where('user_id', auth()->user()->id)->select('owner_id')->groupBy('owner_id')->pluck('owner_id')->toArray();
        $currency_symbol = currency_symbol();
        $shops = [];
        if (!empty($owner_ids)) {
            foreach ($owner_ids as $owner_id) {
                $shop = array();
                $shop_items_raw_data = Cart::where('user_id', auth()->user()->id)->where('owner_id', $owner_id)->get()->toArray();
                $shop_items_data = array();
                if (!empty($shop_items_raw_data)) {
                    foreach ($shop_items_raw_data as $shop_items_raw_data_item) {
                        $product = Product::where('id', $shop_items_raw_data_item["product_id"])->first();
                        $shop_items_data_item["id"] = intval($shop_items_raw_data_item["id"]);
                        $shop_items_data_item["owner_id"] = intval($shop_items_raw_data_item["owner_id"]);
                        $shop_items_data_item["user_id"] = intval($shop_items_raw_data_item["user_id"]);
                        $shop_items_data_item["product_id"] = intval($shop_items_raw_data_item["product_id"]);
                        $shop_items_data_item["product_name"] = $product->getTranslation('name');
                        $shop_items_data_item["product_thumbnail_image"] = uploaded_asset($product->thumbnail_img);
                        $shop_items_data_item["variation"] = $shop_items_raw_data_item["variation"];
                        $shop_items_data_item["price"] = (float) $shop_items_raw_data_item["price"];
                        $shop_items_data_item["currency_symbol"] = $currency_symbol;
                        $shop_items_data_item["tax"] = (float) $shop_items_raw_data_item["tax"];
                        $shop_items_data_item["shipping_cost"] = (float) $shop_items_raw_data_item["shipping_cost"];
                        $shop_items_data_item["quantity"] = intval($shop_items_raw_data_item["quantity"]);
                        $shop_items_data_item["lower_limit"] = intval($product->min_qty);
                        $shop_items_data_item["upper_limit"] = intval($product->stocks->where('variant', $shop_items_raw_data_item['variation'])->first()->qty);

                        $shop_items_data[] = $shop_items_data_item;
                    }
                }


                $shop_data = Shop::where('user_id', $owner_id)->first();
                if ($shop_data) {
                    $shop['name'] = $shop_data->name;
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                } else {
                    $shop['name'] = "Inhouse";
                    $shop['owner_id'] = (int) $owner_id;
                    $shop['cart_items'] = $shop_items_data;
                }
                $shops[] = $shop;
            }
        }

        //dd($shops);

        return response()->json($shops);
    }


    public function add(Request $request)
    {
        try {
            $product = Product::find($request->id);

            if (!$product) {
                return $this->returnError('404', 'هذا المنتج غير موجود');
            }

            $variant = $request->variant;
            $tax = 0;

            $address = auth()->user()->addresses()->first();
            $state = State::find($address->state_id);
            $shipping_cost =  auth()->user()->customer_type == 'wholesale' ? $state->wholesaler_cost : $state->retailer_cost;
            $stateProduct = $product->states->where("state_id", $state->id)->first();
            $price = $stateProduct->cost;
            $qty   = $stateProduct->qty;

            $cart = Cart::where('user_id', auth()->user()->id)->first();
            if ($cart != null) {
                if ($cart->address_id != 0) {
                    $address = Address::find($cart->address_id);
                    $state = State::find($address->state_id);
                    $shipping_cost =  auth()->user()->customer_type == 'wholesale' ? $state->wholesaler_cost : $state->retailer_cost;
                    $stateProduct = $product->states->where("state_id", $state->id)->first();
                    $price = $stateProduct->cost;
                    $qty   = $stateProduct->qty;
                }
            }

            //calculation of taxes
            $discount_applicable = false;
            if ($product->discount_start_date == null) {
                $discount_applicable = true;
            } elseif (
                strtotime(date('d-m-Y H:i:s')) >= $product->discount_start_date &&
                strtotime(date('d-m-Y H:i:s')) <= $product->discount_end_date
            ) {
                $discount_applicable = true;
            }

            if ($discount_applicable) {
                if ($product->discount_type == 'percent') {
                    $price -= ($price * $product->discount) / 100;
                } elseif ($product->discount_type == 'amount') {
                    $price -= $product->discount;
                }
            }

            foreach ($product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'percent') {
                    $tax += ($price * $product_tax->tax) / 100;
                } elseif ($product_tax->tax_type == 'amount') {
                    $tax += $product_tax->tax;
                }
            }

            if ($product->min_qty > $request->quantity) {
                return response()->json(
                    [
                        'result' => false,
                        'message' => translate("Minimum") . " {$product->min_qty} " . translate("item(s) should be ordered")
                    ],
                    200
                );
            }

            if ($qty < $request->quantity) {
                if ($qty == 0) {
                    return response()->json(
                        [
                            'result' => false,
                            'message' => translate('Stock out')
                        ],
                        200
                    );
                } else {
                    return response()->json(
                        [
                            'result' => false,
                            'message' => translate("Only") . " $qty " . translate("item(s) are available")
                        ],
                        200
                    );
                }
            }

            Cart::updateOrCreate([
                'user_id'    => auth()->user()->id,
                'owner_id'   => $product->user_id,
                'product_id' => $request->id,
                'address_id' => $address->id
            ], [
                'price'         => $price,
                'tax'           => $tax,
                'shipping_cost' => $shipping_cost,
                'quantity'      => DB::raw("quantity + $request->quantity")
            ]);

            // if (\App\Utility\NagadUtility::create_balance_reference($request->cost_matrix) == false) {
            //     return response()->json(['result' => false, 'message' => 'Cost matrix error']);
            // }

            return response()->json([
                'result' => true,
                'message' => translate('Product added to cart successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    public function changeQuantity(Request $request)
    {
        try {
            $cart = Cart::find($request->id);
            if ($cart != null) {

                if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
                    $cart->update([
                        'quantity' => $request->quantity
                    ]);

                    return response()->json(['result' => true, 'message' => translate('Cart updated')], 200);
                } else {
                    return response()->json(['result' => false, 'message' => translate('Maximum available quantity reached')], 200);
                }
            }

            return response()->json(['result' => false, 'message' => translate('Something went wrong')], 200);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    public function process(Request $request)
    {
        try {
            $cart_ids = explode(",", $request->cart_ids);
            $cart_quantities = explode(",", $request->cart_quantities);

            $state_id = auth()->user()->addresses()->first()->state_id;
            $cart = Cart::where('user_id', auth()->user()->id)->first();
            if ($cart != null) {
                if ($cart->address_id != 0) {
                    $state_id = Address::find($cart->address_id)->state_id;
                }
            }

            if (!empty($cart_ids)) {
                $i = 0;
                foreach ($cart_ids as $cart_id) {

                    $cart_item = Cart::where('id', $cart_id)->first();
                    $product   = Product::where('id', $cart_item->product_id)->first();

                    if ($product->min_qty > $cart_quantities[$i]) {
                        return response()->json(
                            [
                                'result' => false,
                                'message' => translate("Minimum") . " {$product->min_qty} " . translate("item(s) should be ordered for") . " {$product->name}"
                            ],
                            200
                        );
                    }

                    $state = $product->states->where("state_id", $state_id)->first();
                    $qty = $state->qty;

                    if ($qty >= $cart_quantities[$i]) {
                        $cart_item->update(
                            [
                                'quantity' => $cart_quantities[$i]
                            ]
                        );
                    } else {
                        if ($qty == 0) {
                            return response()->json(
                                [
                                    'result' => false,
                                    'message' => translate("No item is available for") . " {$product->name} " . translate("remove this from cart")
                                ],
                                200
                            );
                        } else {
                            return response()->json(
                                [
                                    'result' => false,
                                    'message' => translate("Only") . " {$qty} " . translate("item(s) are available for") . " {$product->getTranslation('name')}"
                                ],
                                200
                            );
                        }
                    }

                    $i++;
                }

                return response()->json(
                    [
                        'result' => true,
                        'message' => translate('Cart updated')
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'result' => false,
                        'message' => translate('Cart is empty')
                    ],
                    200
                );
            }
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    public function destroy($id)
    {
        Cart::destroy($id);
        return response()->json(['result' => true, 'message' => translate('Product is successfully removed from your cart')], 200);
    }
}