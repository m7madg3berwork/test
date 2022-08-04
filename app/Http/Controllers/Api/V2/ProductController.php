<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\ProductCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Http\Resources\V2\FlashDealCollection;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Color;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use App\Utility\SearchUtility;
use Cache;
use Laravel\Sanctum\PersonalAccessToken;

class ProductController extends Controller
{
    public function index(Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }


        // return auth()->user()->city_id;

        $products = [];

        if ($request->customer_type == 1) {
            $products = Product::where('wholesale_product', 1)->paginate(10);
        } else {
            $products = Product::where('wholesale_product', 0)->paginate(10);
        }

        // return $products[$i]->zones->where('zone_id' , auth()->user()->city_id)->first()->cost;
        if ($request->header('auth')) {
            foreach ($products as $key => $product) {
                $product['zone_price'] = 0;
                $product['zone_qty'] = 0;
                if (count($product->zones) != 0) {
                    $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    $product['zone_qty'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->zone_qty;
                }
            }
        }

        return new ProductMiniCollection($products);
    }

    public function show($id, Request $request)
    {
        // get first address
        $address = auth()->user()->addresses()->first();
        if ($address == null) {
            return response()->json([
                'message' => 'Address not found',
                'code' => 404
            ]);
        }

        // get products
        $products = Product::where("id", $id)->get();

        // get product zone based on address
        $zone = $products[0]->zones->where("zone_id", $address->zone_id)->first();

        $products[0]->main_price = $zone->cost;

        return new ProductDetailCollection($products);
    }

    public function admin(Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        $products = [];

        if ($request->customer_type == 1) {
            $products = Product::where(['added_by' => 'admin', 'wholesale_product' => 1])->paginate(10);
        } else {
            $products = Product::where(['added_by' => 'admin', 'wholesale_product' => 0])->paginate(10);
        }

        // return $products[$i]->zones->where('zone_id' , auth()->user()->city_id)->first()->cost;

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
    }

    public function seller($id, Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        $shop = Shop::findOrFail($id);


        if ($request->customer_type == 1) {
            $products = Product::where('added_by', 'seller')->where('user_id', $shop->user_id)->where('wholesale_product', 1);
        } else {
            $products = Product::where('added_by', 'seller')->where('user_id', $shop->user_id)->where('wholesale_product', 0);
        }



        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }
        $products->where('published', 1);

        $products = $products->latest()->paginate(10);

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
    }

    public function category($id, Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;

        if ($request->customer_type == 1) {
            $products = Product::whereIn('category_id', $category_ids)->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::whereIn('category_id', $category_ids)->where('wholesale_product', 0)->physical();
        }



        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        $products = filter_products($products)->latest()->paginate(10);

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
    }


    public function brand($id, Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        if ($request->customer_type == 1) {
            $products = Product::where('brand_id', $id)->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::where('brand_id', $id)->where('wholesale_product', 0)->physical();
        }

        if ($request->name != "" || $request->name != null) {
            $products = $products->where('name', 'like', '%' . $request->name . '%');
        }

        $products = filter_products($products)->latest()->paginate(10);

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
    }



    public function todaysDeal(Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        if ($request->customer_type == 1) {
            $products = Product::where('todays_deal', 1)->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::where('todays_deal', 1)->where('wholesale_product', 0)->physical();
        }

        $products = filter_products($products)->limit(20)->latest()->get();

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
    }

    public function flashDeal(Request $request)
    {
        return Cache::remember('app.flash_deals', 86400, function () {
            $flash_deals = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
            return new FlashDealCollection($flash_deals);
        });
    }

    public function featured(Request $request)
    {
        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        if ($request->customer_type == 1) {
            $products = Product::where('featured', 1)->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::where('featured', 1)->where('wholesale_product', 0)->physical();
        }
        return new ProductMiniCollection(filter_products($products)->latest()->paginate(10));
    }

    public function bestSeller(Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }



        // return Cache::remember('app.best_selling_products', 86400, function () use ($request) {
        if ($request->customer_type == 1) {
            $products = Product::orderBy('num_of_sale', 'desc')->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::orderBy('num_of_sale', 'desc')->where('wholesale_product', 0)->physical();
        }

        $products = filter_products($products)->limit(20)->get();

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
        // });
    }

    public function related($id, Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }

        // return Cache::remember("app.related_products-$id", 86400, function () use ($id) {
        $product = Product::find($id);

        if ($request->customer_type == 1) {
            $products = Product::where('category_id', $product->category_id)->where('id', '!=', $id)->where('wholesale_product', 1)->physical();
        } else {
            $products = Product::where('category_id', $product->category_id)->where('id', '!=', $id)->where('wholesale_product', 0)->physical();
        }

        $products = filter_products($products)->limit(10)->get();

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);
        // });
    }

    public function topFromSeller($id, Request $request)
    {

        $auth = $request->header('auth');
        if ($auth && $auth != null) {
            try {

                $token = PersonalAccessToken::findToken($auth)->first();
                if (!$token) 'Token not found';
                $user = $token->tokenable;
                // if($user)return $user;
                // else return 'User not found';

            } catch (\Throwable $th) {
                //throw $th;
                return response()->json([
                    'message' => 'User not found',
                    'code' => 404
                ]);
            }
        }


        // return Cache::remember("app.top_from_this_seller_products-$id", 86400, function () use ($id) {
        $product = Product::find($id);
        $products = Product::where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->physical();

        $products = filter_products($products)->limit(10)->get();

        if (count($products) > 0) {
            if ($request->header('auth')) {
                foreach ($products as $key => $product) {
                    $product['zone_price'] = 0;
                    if (count($product->zones) != 0) {
                        $product['zone_price'] = $product->zones->where('zone_id', auth()->user()->city_id)->first()->cost;
                    }
                }
            }
        }

        return new ProductMiniCollection($products);

        // });
    }


    public function search(Request $request)
    {
        $category_ids = [];
        $brand_ids = [];

        if ($request->categories != null && $request->categories != "") {
            $category_ids = explode(',', $request->categories);
        }

        if ($request->brands != null && $request->brands != "") {
            $brand_ids = explode(',', $request->brands);
        }

        $sort_by = $request->sort_key;
        $name = $request->name;
        $min = $request->min;
        $max = $request->max;


        $products = Product::query();

        $products->where('published', 1)->physical();

        if (!empty($brand_ids)) {
            $products->whereIn('brand_id', $brand_ids);
        }

        if (!empty($category_ids)) {
            $n_cid = [];
            foreach ($category_ids as $cid) {
                $n_cid = array_merge($n_cid, CategoryUtility::children_ids($cid));
            }

            if (!empty($n_cid)) {
                $category_ids = array_merge($category_ids, $n_cid);
            }

            $products->whereIn('category_id', $category_ids);
        }

        if ($name != null && $name != "") {
            $products->where(function ($query) use ($name) {
                foreach (explode(' ', trim($name)) as $word) {
                    $query->where('name', 'like', '%' . $word . '%')->orWhere('tags', 'like', '%' . $word . '%')->orWhereHas('product_translations', function ($query) use ($word) {
                        $query->where('name', 'like', '%' . $word . '%');
                    });
                }
            });
            SearchUtility::store($name);
        }

        if ($min != null && $min != "" && is_numeric($min)) {
            $products->where('unit_price', '>=', $min);
        }

        if ($max != null && $max != "" && is_numeric($max)) {
            $products->where('unit_price', '<=', $max);
        }

        switch ($sort_by) {
            case 'price_low_to_high':
                $products->orderBy('unit_price', 'asc');
                break;

            case 'price_high_to_low':
                $products->orderBy('unit_price', 'desc');
                break;

            case 'new_arrival':
                $products->orderBy('created_at', 'desc');
                break;

            case 'popularity':
                $products->orderBy('num_of_sale', 'desc');
                break;

            case 'top_rated':
                $products->orderBy('rating', 'desc');
                break;

            default:
                $products->orderBy('created_at', 'desc');
                break;
        }

        return new ProductMiniCollection(filter_products($products)->paginate(10));
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        $tax = 0;

        if ($request->has('color') && $request->color != "") {
            $str = Color::where('code', '#' . $request->color)->first()->name;
        }

        $var_str = str_replace(',', '-', $request->variants);
        $var_str = str_replace(' ', '', $var_str);

        if ($var_str != "") {
            $temp_str = $str == "" ? $var_str : '-' . $var_str;
            $str .= $temp_str;
        }


        $product_stock = $product->stocks->where('variant', $str)->first();
        $price = $product_stock->price;
        $stockQuantity = $product_stock->qty;


        //discount calculation
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

        if ($product->tax_type == 'percent') {
            $price += ($price * $product->tax) / 100;
        } elseif ($product->tax_type == 'amount') {
            $price += $product->tax;
        }



        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (float)convert_price($price),
            'price_string' => format_price(convert_price($price)),
            'stock' => intval($stockQuantity),
            'image' => $product_stock->image == null ? "" : uploaded_asset($product_stock->image)
        ]);
    }

    public function home()
    {
        return new ProductCollection(Product::inRandomOrder()->physical()->take(50)->get());
    }
}