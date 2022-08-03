<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\V2\PurchaseHistoryMiniCollection;
use App\Http\Resources\V2\PurchaseHistoryCollection;
use App\Http\Resources\V2\PurchaseHistoryItemsCollection;
use App\Models\CommercialPhotos;
use App\Models\OrderDetail;

class SellerController extends Controller
{

    public function commercialPhoto(Request $request)
    {


        // return auth()->user()->user_type;
        if (auth()->user()->user_type != "seller") {
            return response()->json([
                'msg' => 'The user type must be a seller',
                'status' => "401"
            ]);
        }

        $validate = Validator($request->all(), [
            'image' => "required|image",
            'type' => "required|string|in:tax_number_certificate,commercial_registry"
        ]);

        
        $image_count = CommercialPhotos::where('user_id', auth()->user()->id)->where('type', $request->type)->count();
        if ($image_count >= 1) {
            return response()->json([
                'msg' => 'You can not upload more than 1 '.$request->type.' image',
                'status' => "401"
            ]);
        }

        if ($validate->fails()) {
            return $validate->errors()->first();
        }

        
            $image = $request->file('image');
            $fileName = time() . $image->getClientOriginalName();
            $image->move(public_path("/assets/img/commercial/"), $fileName);
            $photoURL = url("public/assets/img/commercial/" . $fileName);

           

            CommercialPhotos::create([
                'user_id' => auth()->user()->id,
                'image' => $photoURL,
                'type' => $request->type,
            ]);


            return response()->json([
                'msg' => 'File Uploaded',
                'status' => "200"
            ]);
            
            // return response()->json([
            //     'mess' => 'not hereeeeeeeeeeee',
            // ]);;
            
    }


    function getOrderList(Request $request)
    {
        $order_query = Order::query();
        if ($request->payment_status != "" || $request->payment_status != null) {
            $order_query->where('payment_status', $request->payment_status);
        }
        if ($request->delivery_status != "" || $request->delivery_status != null) {
            $delivery_status = $request->delivery_status;
            $order_query->whereIn("id", function ($query) use ($delivery_status) {
                $query->select('order_id')
                    ->from('order_details')
                    ->where('delivery_status', $delivery_status);
            });
        }

        return new PurchaseHistoryMiniCollection($order_query->where('seller_id', auth()->user()->id)->latest()->paginate(5));
    }


    function getOrderDetails($id)
    {
        $order_detail = Order::where('id', $id)->where('seller_id', auth()->user()->id)->get();
        // $order_query = auth()->user()->orders->where('id', $id);

        // return new PurchaseHistoryCollection($order_query->get());
        return new PurchaseHistoryCollection($order_detail);
    }

    function getOrderItems($id)
    {
        $order_id = Order::select('id')->where('id', $id)->where('seller_id', auth()->user()->id)->first();
        $order_query = OrderDetail::where('order_id', $order_id->id);
        return new PurchaseHistoryItemsCollection($order_query->get());
    }

    public function getCommercialPhotos()
    {
        return response()->json([
            'data' => auth()->user()->commercial_photos,
            'status' => "200"
        ]);
    }


}
