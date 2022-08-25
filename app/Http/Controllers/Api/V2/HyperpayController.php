<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WishlistCollection;
use App\Models\Cart;
use App\Models\ComparisonProducts;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\UserPackage;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class HyperpayController extends Controller
{

    use GeneralTrait;

    /**
     * Get Package Checkout ID
     */
    public function getPackageCheckoutId($id)
    {
        try {
            $user_package = UserPackage::findOrFail($id);

            $total = $user_package->package->price;

            $url = config('payment.hyperpay.url') . "/v1/checkouts";
            $data = "entityId=" . config('payment.hyperpay.entity_id') . "&amount=" . $total . "&currency=EUR" . "&paymentType=DB";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer ' . config('payment.hyperpay.auth_key')
            ));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, config('payment.hyperpay.production'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);
            $res = json_decode($responseData, true);

            return response()->json([
                'status'      => true,
                'data'        => $res,
                'total_price' => $total,
                'checkout_id' => $res['id'],
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'result' => false,
                    'message' => translate('Oops...')
                ]
            );
        }
    }
    public function getPackagePaymentStatus(Request $request)
    {
        try {
            $validate = Validator($request->all(), [
                'resource_path'   => "required|string",
                'user_package_id' => "required",
            ]);

            if ($validate->fails()) {
                $code = $this->returnCodeAccordingToInput($validate);
                return $this->returnValidationError($code, $validate);
            }

            $user_package = UserPackage::findOrFail($request->user_package_id);

            $url = config('payment.hyperpay.url');
            $url .= $request->resource_path;
            $url .= "?entityId=" . config('payment.hyperpay.entity_id');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization:Bearer ' . config('payment.hyperpay.auth_key')
            ));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, config('payment.hyperpay.production'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $responseData = curl_exec($ch);
            if (curl_errno($ch)) {
                return curl_error($ch);
            }
            curl_close($ch);

            $res = json_decode($responseData, true);

            //if the payment was done:
            if ($res['result']['code'] == "000.100.110") {

                // update package status
                $user_package->payment_status = 'paid';
                $user_package->save();

                return response()->json([
                    'status'         => true,
                    'msg'            => translate('Payment Succeeded with Updating Package information'),
                    'transaction_id' => $res['id'],
                    'data'           => $res
                ]);
            }

            return response()->json(
                [
                    'status' => true,
                    'msg' => translate('Payment Failed'),
                    'data' => $res
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'result' => false,
                    'message' => translate('Oops...')
                ]
            );
        }
    }

    public function getCheckoutId()
    {

        // $validate = Validator($request->all(), [
        //     'price' => "required|numeric|min:1"
        // ]);

        // if ($validate->fails()) {
        //     return $validate->errors()->first();
        // }

        $items = auth()->user()->carts;
        if ($items->isEmpty()) {
            return response()->json([
                'msg' => 'Your cart is empty',
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

        // return $subtotal;

        $url = config('payment.hyperpay.url') . "/v1/checkouts";
        $data = "entityId=" . config('payment.hyperpay.entity_id') . "&amount=" . $subtotal . "&currency=EUR" . "&paymentType=DB";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer ' . config('payment.hyperpay.auth_key')
        ));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, config('payment.hyperpay.production')); // this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);
        $res = json_decode($responseData, true);


        return response()->json([
            'status' => true,
            'data' => $res,
            'total_price' => $subtotal,
            'checkout_id' => $res['id'],
        ]);
    }


    public function getPaymentStatus(Request $request)
    {

        $validate = Validator($request->all(), [
            'resource_path' => "required|string",
            'orders_id' => "required|string",
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $orders_ids_arr = explode(",", $request->orders_id);
        foreach ($orders_ids_arr as $key => $order_id) {
            $order = Order::find($order_id);
            if (!$order) {
                return $this->returnError('404', "No order with id = " . $order_id);
            }
        }


        $url = config('payment.hyperpay.url');
        // $url .= '/v1/checkouts/2E5A18471E1AF6FB114B54386F37ECB5.uat01-vm-tx02/payment';        // $resourcePath
        $url .= $request->resource_path;        // $resourcePath
        $url .= "?entityId=" . config('payment.hyperpay.entity_id');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization:Bearer ' . config('payment.hyperpay.auth_key')
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, config('payment.hyperpay.production')); // this should be set to true in production
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        if (curl_errno($ch)) {
            return curl_error($ch);
        }
        curl_close($ch);

        $res = json_decode($responseData, true);

        //         return $res['result']['code'];

        if ($res['result']['code'] == "000.100.110") {    //if the payment was done:

            //Update orders info after payment was done
            foreach ($orders_ids_arr as $key => $order_id) {
                $order = Order::find($order_id);
                if (!$order) {  //Make sure of the orders again
                    return response()->json([
                        'status' => 404,
                        'msg' => "No order with id = " . $order_id,
                    ]);
                }
                $order->payment_type = 'hyperpay';
                $order->payment_status = 'paid';
                $order->payment_details = $res['id'];   //Save transaction id
                $order->save();

                Cart::where('user_id', auth()->user()->id)->delete();
            }

            return response()->json([
                'status' => true,
                'msg' => "Payment Succeeded with Updating Orders information",
                'transaction_id' => $res['id'],
                'data' => $res
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => "Payment Failed",
            'data' => $res,
            // 'data' => $res['id']
        ]);
    }
}