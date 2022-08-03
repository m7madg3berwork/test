<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\WishlistCollection;
use App\Models\ComparisonProducts;
use App\Models\Wishlist;
use App\Models\Product;
use App\Traits\GeneralTrait;
use Illuminate\Http\Request;

class ComparisonProductsController extends Controller
{

    use GeneralTrait;

    public function index()
    {
        $product_ids = ComparisonProducts::where('user_id', auth()->user()->id)->pluck("product_id")->toArray();
        $existing_product_ids = Product::whereIn('id', $product_ids)->pluck("id")->toArray();

        $query = ComparisonProducts::query();
        $query->where('user_id', auth()->user()->id)->whereIn("product_id", $existing_product_ids);

        return new WishlistCollection($query->latest()->get());
    }


    public function checkProductInComparisonList(Request $request)
    {

        $validate = Validator($request->all(), [
            'product_id' => "required|integer"
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }


        $product = ComparisonProducts::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->count();
        if ($product > 0) {
            return response()->json([
                'message' => translate('Product already added to your comparison list'),
                'is_in_comp_prods' => true,
                'product_id' => (int)$request->product_id,
                'status' => "200"
            ], 200);
        } else {
            return response()->json([
                'message' => "Product not in your comparison list",
                'is_in_comp_prods' => false,
                'product_id' => (int)$request->product_id,
                'status' => "200"
            ], 404);
        }
    }





    public function add(Request $request)
    {
        $comp_prods_count = ComparisonProducts::where(['user_id' => auth()->user()->id])->count();
        // return $comp_prods_count;
        if ($comp_prods_count >= 3) {
            return $this->returnError('301', translate('You can not add more than 3 products in your comparison products'));
        } else {
            $validate = Validator($request->all(), [
                'product_id' => "required|integer|exists:products,id"
            ]);

            if ($validate->fails()) {
                $code = $this->returnCodeAccordingToInput($validate);
                return $this->returnValidationError($code, $validate);
            }

            $product = ComparisonProducts::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->count();
            if ($product > 0) {
                return response()->json([
                    'message' => translate('Product present in comparison list'),
                    'is_in_comp_prods' => true,
                    'product_id' => (int)$request->product_id,
                    'status' => "200"
                ], 200);
            } else {
                ComparisonProducts::create(
                    ['user_id' => auth()->user()->id, 'product_id' => $request->product_id]
                );

                return response()->json([
                    'message' => translate('Product added to comparison list'),
                    'is_in_comp_prods' => true,
                    'product_id' => (int)$request->product_id,
                    'status' => "200"
                ], 200);
            }
        }
    }

    public function remove(Request $request)
    {
        $validate = Validator($request->all(), [
            'product_id' => "required|integer"
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $product = ComparisonProducts::where(['product_id' => $request->product_id, 'user_id' =>  auth()->user()->id])->count();
        if ($product == 0) {
            return response()->json([
                'message' => translate('Product is not in your comparison list'),
                'is_in_comp_prods' => false,
                'product_id' => (int)$request->product_id,
                'status' => "200"
            ], 200);
        } else {
            ComparisonProducts::where(['product_id' => $request->product_id, 'user_id' => auth()->user()->id])->delete();

            return response()->json([
                'message' => translate('Product is removed from comparison list'),
                'is_in_comp_prods' => false,
                'product_id' => (int)$request->product_id,
                'status' => "200"
            ], 200);
        }
    }
}
