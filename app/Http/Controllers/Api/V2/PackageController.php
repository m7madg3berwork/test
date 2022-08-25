<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Resources\V2\PackageCollection;
use App\Http\Resources\V2\PackageDetailCollection;
use App\Http\Resources\V2\ProductDetailCollection;
use App\Http\Resources\V2\ProductMiniCollection;
use Illuminate\Http\Request;
use App\Models\Attribute;
use App\Models\Color;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\PackageShippingDays;
use App\Models\PackageTranslation;
use App\Models\Product;
use App\Models\Units;
use App\Models\UnitsTranslation;
use App\Models\UserPackage;
use App\Models\WeekDays;
use App\Traits\GeneralTrait;
use Carbon\Carbon;
use CoreComponentRepository;
use Str;

class PackageController extends Controller
{
    use GeneralTrait;

    /**
     * Get All Packages Without Auth
     */
    public function getAll(Request $request)
    {
        $packages = Package::where('added_by', 'admin')
            ->where("active", 1);

        $customer_type = $request->customer_type;
        if ($customer_type != null) {
            $packages = $packages->where('customer_type', $customer_type);
        }

        $packages = $packages->latest()
            ->paginate(10);

        return new PackageCollection($packages);
    }

    /**
     * Get All Packages Based On Auth
     */
    public function authGetAll(Request $request)
    {
        try {
            $address = auth()->user()->addresses()->where('set_default', 1)->first();

            $packages = Package::where('added_by', 'admin')
                ->where("active", 1)
                ->where('customer_type', auth()->user()->customer_type)
                ->whereHas('states', function ($q) use ($address) {
                    $q->where("states.id", $address->state_id);
                })
                ->latest()
                ->paginate(10);

            return new PackageCollection($packages);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    /**
     * Get Package Detail
     */
    public function getPackage(Request $request, $id)
    {
        try {
            $package = Package::findOrFail($id);

            $products = $package->products;
            $productsData = [];
            foreach ($products as $product) {
                $productsData[] = [
                    'id' => $product->id,
                    'name' => $product->getTranslation('name'),
                    'price' => format_price($product->unit_price),
                    'qty' => $product->pivot->qty
                ];
            }

            return response()->json([
                'result' => true,
                'data' => [
                    'id'            => $package->id,
                    'name'          => $package->getTranslation('name'),
                    'desc'          => $package->getTranslation('desc'),
                    'customer_type' => $package->customer_type,
                    'price'         => ceil($package->price),
                    'show_price'    => format_price(convert_price($package->price)),
                    'qty'           => $package->package_items ? $package->package_items->sum('qty') : 0,
                    'shipping_type' => $package->shipping_type,
                    'duration'      => $package->duration,
                    'visits_num'    => $package->visits_num,
                    'states'        => $package->states,
                    'products'      => $productsData
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => translate('Oops...')
            ]);
        }
    }

    /**
     * Subscribe Package
     */
    public function subscribe(Request $request)
    {
        try {
            $validate = Validator($request->all(), [
                'package_id' => "required|integer",
                'days'       => "required|string|max: 255",
                'times'      => "required|string|max: 255",
            ]);

            if ($validate->fails()) {
                $code = $this->returnCodeAccordingToInput($validate);
                return $this->returnValidationError($code, $validate);
            }

            /**
             * Check if user can subscribe or not
             */
            $address = auth()->user()->addresses()->where('set_default', 1)->first();
            $package = Package::where("id", $request->package_id)
                ->where('added_by', 'admin')
                ->where("active", 1)
                ->where('customer_type', auth()->user()->customer_type)
                ->whereHas('states', function ($q) use ($address) {
                    $q->where("states.id", $address->state_id);
                })
                ->first();
            if ($package == null) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => translate('You can\'t subscribe this Package.')
                    ]
                );
            }

            $days = explode(",", $request->days);
            $times = explode(",", $request->times);
            foreach ($days as $key => $day_id) {
                $day = WeekDays::find($day_id);
                if (!$day) {
                    return response()->json(
                        [
                            'result' => false,
                            'message' => translate('Please enter correct day.')
                        ]
                    );
                }
                if ($day->active == 0) {
                    return response()->json(
                        [
                            'result' => false,
                            'message' => translate('This day not available for shipping.')
                        ]
                    );
                }
            }

            $user_package = UserPackage::where('package_id', $request->package_id)
                ->where('user_id', auth()->user()->id)
                ->where('is_active', 1)
                ->first();
            if (isset($user_package)) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => translate('You already subscribed on this Package.')
                    ]
                );
            }

            if (count(array_filter($days, function ($x) {
                return ($x !== "");
            })) != count(array_filter($times, function ($x) {
                return ($x !== "");
            }))) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => translate('Number of Days don\'t Match Number of Times.')
                    ]
                );
            }

            $new_user_package = new UserPackage();
            $new_user_package->package_id       = $request->package_id;
            $new_user_package->user_id          = auth()->user()->id;
            $new_user_package->start_date       = Carbon::now()->toDateString();
            $new_user_package->end_date         = Carbon::now()->addMonth($package->duration)->toDateString();
            $new_user_package->remaining_visits = $package->visits_num;
            $new_user_package->days             = $request->days;
            $new_user_package->times            = $request->times;
            $new_user_package->is_active        = 1;
            $new_user_package->payment_status   = 'unpaid';
            $new_user_package->save();

            return response()->json(
                [
                    'result'          => true,
                    'message'         => translate('You subscribe on this Package.'),
                    'user_package_id' => $new_user_package->id
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'result' => false,
                    'message' => translate('This package does not exist.')
                ]
            );
        }
    }

    public function mySubscribed()
    {
        try {
            $user_packages = auth()->user()->user_packages;

            $packagesData = [];
            foreach ($user_packages as $user_package) {
                $packagesData[] = [
                    'id'               => $user_package->package->id,
                    'name'             => $user_package->package->getTranslation('name'),
                    'price'            => format_price($user_package->package->price),
                    'qty'              => $user_package->package->products ? $user_package->package->package_items->sum('qty') : 0,
                    'start_date'       => $user_package->start_date,
                    'end_date'         => $user_package->end_date,
                    'remaining_visits' => $user_package->remaining_visits,
                    'days'             => $user_package->days,
                    'times'            => $user_package->times,
                    'is_active'        => $user_package->is_active,
                    'payment_status'   => $user_package->payment_status,
                ];
            }

            return response()->json(
                [
                    'result' => true,
                    'data'   => $packagesData
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'result' => false,
                    'message' => translate('This package does not exist.')
                ]
            );
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function adminPackages()
    {

        return new PackageCollection(Package::with('package_items')->where('added_by', 'admin')->latest()->paginate(10));

        // return response()->json([
        //     'data' => $packages,
        //     'status' => '200'
        // ], 200);
    }

    public function adminPackage($id)
    {
        $package = Package::with('package_items')->where('added_by', 'admin')->find($id);
        if (!$package) {
            return response()->json([
                'package' => 'Not Found This Package',
                'status' => '404'
            ], 404);
        }
        $package_items = $package->package_items;
        if ($package_items) {
            foreach ($package_items as $item) {
                $prod = Product::where('id', $item->product_id)->first();
                $item['product'] = [];

                if (!empty($prod)) {
                    $item['product'] = [
                        'id' => $prod->id,
                        'name' => $prod->getTranslation('name'),
                        'added_by' => $prod->added_by,
                        'wholesale_product' => $prod->wholesale_product == 0 ? 'wholesale' : 'retail',
                        'thumbnail_image' => uploaded_asset($prod->thumbnail_img),
                        'has_discount' => home_base_price($prod, false) != home_discounted_base_price($prod, false),
                        'stroked_price' => home_base_price($prod),
                        'main_price' => home_discounted_base_price($prod),
                        'rating' => (float)$prod->rating,
                        'sales' => (int)$prod->num_of_sale,
                        'unit' => $prod->unit,
                        'return_policy' => $prod->return_policy,
                        'zones' => $prod->zones()->pluck('zone_id')->toArray(),
                        'category' => [
                            'id' => optional($prod->category)->id,
                            'name' => optional($prod->category)->getTranslation('name')
                        ],
                        'brand' => [
                            'id' => optional($prod->brand)->id,
                            'brand_name' => optional($prod->brand)->getTranslation('name')
                        ],
                        'links' => [
                            'details' => route('products.show', $prod->id),
                        ]
                    ];
                }
            }
        }

        return [

            'id' => $package->id,
            'name' => $package->getTranslation('name'),
            'desc' => $package->getTranslation('desc'),
            'added_by' => $package->added_by,
            'user_id' => $package->user_id,
            'customer_type' => $package->customer_type,
            'price' => $package->price,
            'qty' => $package->qty,
            'created_at' => $package->created_at,
            'package_items' => $package->package_items,
            'success' => true,
            'status' => 200

        ];

        // return new PackageDetailCollection($package);
        // return response()->json([
        //     $package,
        //     'status' => '200'
        // ], 200);
    }


    public function subscribePackage(Request $request)
    {
        // return auth()->user()->id;
        // return $request;

        $validate = Validator($request->all(), [
            'package_id' => "required|integer",
            'days' => "required|string|max:255",
            'times' => "required|string|max:255",
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $days = explode(",", $request->days);
        $times = explode(",", $request->times);

        foreach ($days as $key => $day_id) {
            $day = WeekDays::find($day_id);
            if (!$day) {
                return $this->returnError('404', 'برجاء ادخال يوم صحيح');
            }
            if ($day->active == 0) {
                return $this->returnError('404', 'هذا اليوم غير متاح للتوصيل');
            }
        }

        $package = Package::find($request->package_id);

        if (!$package) {
            return $this->returnError('404', 'هذا اليوم غير متاح للتوصيل');
            return response()->json([
                'message' => 'This package does not exist..!',
                'is_in_package_items' => false,
                'status' => "404"
            ], 404);
        }

        $user_package = UserPackage::where('package_id', $request->package_id)->where('user_id', auth()->user()->id)->first();
        if (isset($user_package)) {
            return response()->json([
                'message' => 'You already subscribed on this Package',
                'item_id' => (int)$user_package->id,
                'status' => '301'
            ], 301);
        }

        if (count(array_filter($days, function ($x) {
            return ($x !== "");
        })) != count(array_filter($times, function ($x) {
            return ($x !== "");
        }))) {

            return $this->returnError('301', 'Number of Days don\'t Match Number of Times');
        }

        $new_user_package = new UserPackage();

        $new_user_package->package_id = $request->package_id;
        $new_user_package->user_id = auth()->user()->id;
        $new_user_package->start_date = Carbon::now()->toDateString();
        $new_user_package->end_date = Carbon::now()->addMonth($package->duration)->toDateString();
        $new_user_package->remaining_visits = $package->visits_num;
        $new_user_package->days = $request->days;
        $new_user_package->times = $request->times;
        $new_user_package->is_active = 0;

        $new_user_package->save();

        return response()->json([
            'message' => 'You subscribe on this Package',
            'package_id' => (int)$new_user_package->id,
            'status' => '200'
        ], 200);
    }


    public function subscribePackageShow($package_id)
    {
        $user_package = UserPackage::with('package')->where(['user_id' => auth()->user()->id, 'package_id' => $package_id])->first();
        if (!$user_package) {
            return $this->returnError('404', translate('Not Found This User Package'));
        }

        $user_package_days = explode(",", $user_package->days);

        $days = WeekDays::whereIn('id', $user_package_days)->get();
        $days_names = [];
        foreach ($days as $key => $day) {
            $days_names[] = $day->name;
        }
        // ->getTranslation('name')

        return [

            'id' => $user_package->id,
            'user_id' => $user_package->user_id,
            'package_id' => $user_package->package_id,
            'start_date' => $user_package->start_date,
            'end_date' => $user_package->end_date,
            'remaining_visits' => $user_package->remaining_visits,
            'days' => implode(',', $days_names),
            'times' => $user_package->times,
            'success' => true,
            'status' => 200

        ];
    }




    public function add(Request $request)
    {
        // return 'App\Http\Controllers\Api\V2\PackageController';

        if ($request->has('is_active')) {
            $request->is_active = 1;
        } else {
            $request->is_active = 0;
        }


        $validate = Validator($request->all(), [
            'name' => "required|min:3|max:50",
            'desc' => "required|string|min:3|max:200",
            'shipping_type' => "required|string|in:weekly,monthly",
            'duration' => 'required|integer',
            'dates' => "required|string|max:255",
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        // return $request->dates;

        $package_name = PackageTranslation::where(['name' => $request->name])->get();
        // return (auth()->user()->id == $package_name->package->user_id)? 'true' : 'false';
        if ($package_name) {
            foreach ($package_name as $key => $item) {
                if ($item->package)
                    if ($item->package->user_id == auth()->user()->id) {
                        // return 'The name has already been taken.';
                        return $this->returnError('404', 'The name has already been taken.');
                    }
            }
        }


        $package = new Package();
        $package->name = $request->name;
        $package->desc = $request->desc;
        $package->added_by = 'user';
        $package->user_id = auth()->user()->id;
        $package->shipping_type = $request->shipping_type;
        $package->duration = $request->duration;

        if ($request->shipping_type == 'weekly') {
            $days = explode(",", $request->dates);
            foreach ($days as $key => $day_id) {
                if (!$day = WeekDays::find($day_id)) {
                    return $this->returnError('404', 'برجاء ادخال يوم صحيح');
                }
            }
        }


        $package->save();

        $package_translation = PackageTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'package_id' => $package->id]);
        $package_translation->name = $request->name;
        $package_translation->desc = $request->desc;
        $package_translation->save();

        $dates = explode(",", $request->dates);

        foreach ($dates as $key => $date) {
            PackageShippingDays::create([
                'package_id' => $package->id,
                'date' => $date
            ]);
        }

        return response()->json([
            'message' => 'Package Added',
            'package_id' => (int)$package->id,
            'package' => $package,
            'status' => '200'
        ], 200);
    }






    public function userPackages()
    {

        return new PackageCollection(Package::with('package_items')->where('added_by', 'user')->where('user_id', auth()->user()->id)->latest()->paginate(10));

        // $packages = Package::with('package_items')->where('added_by', 'user')->where('user_id', auth()->user()->id)->latest()->paginate(10);

        // return response()->json([
        //     'user_packages' => $packages,
        //     'status' => '200'
        // ], 200);


    }

    public function userPackage($id)
    {
        $package = Package::with('package_items')->where('added_by', 'user')->where('id', $id)->where('user_id', auth()->user()->id)->first();
        if (!$package) {
            return $this->returnError('404', 'Not Found This Package.');
        }
        $package_items = $package->package_items;
        if ($package_items) {
            foreach ($package_items as $key => $item) {
                $prod = Product::where('id', $item->product_id)->first();
                $item['product']  = [];
                if (!empty($prod)) {
                    $item['product'] = [
                        'id' => $prod->id,
                        'name' => $prod->getTranslation('name'),
                        'added_by' => $prod->added_by,
                        'wholesale_product' => $prod->wholesale_product == 0 ? 'wholesale' : 'retail',
                        'thumbnail_image' => uploaded_asset($prod->thumbnail_img),
                        'has_discount' => home_base_price($prod, false) != home_discounted_base_price($prod, false),
                        // 'stroked_price' => home_base_price($prod),
                        'main_price' => $prod->unit_price,
                        // 'rating' => (float)$prod->rating,
                        // 'sales' => (int)$prod->num_of_sale,
                        'unit' => $prod->unit,
                        // 'return_policy' => $prod->return_policy,
                        // 'zones' => $prod->zones()->pluck('zone_id')->toArray(),
                        // 'category' => [
                        //     'id' => $prod->category->id,
                        //     'name' => $prod->category->getTranslation('name')
                        // ],
                        // 'brand' => [
                        //     'id' => $prod->brand->id,
                        //     'brand_name' => $prod->brand->getTranslation('name')
                        // ],
                        // 'links' => [
                        //     'details' => route('products.show', $prod->id),
                        // ]
                    ];
                }
            }
        }

        $package_shipping_days = $package->package_shipping_days;

        if ($package->shipping_type == 'weekly') {
            foreach ($package_shipping_days as $key => $day) {
                $day->date = $day->week_day->name;
            }
        }
        // return $package_shipping_days;

        return [

            'id' => $package->id,
            'name' => $package->getTranslation('name'),
            'desc' => $package->getTranslation('desc'),
            // 'added_by' => $package->added_by,
            'price' => $package->price,
            'qty' => $package->qty,
            'shipping_type' => $package->shipping_type,
            'duration' => $package->duration,
            'package_items' => $package->package_items,
            'package_shipping_days' => $package_shipping_days,
            'created_at' => $package->created_at,
            'success' => true,
            'status' => 200

        ];
        // return response()->json([
        //     'package' => $package,
        //     'status' => '200'
        // ], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    public function process(Request $request)
    {
        $packages_items_ids = explode(",", $request->packages_items_ids);
        $quantities = explode(",", $request->quantities);

        if (!empty($packages_items_ids)) {
            $i = 0;
            foreach ($packages_items_ids as $id) {
                // $cart_item = Cart::where('id', $cart_id)->first();

                $package_item = PackageItem::find($id);
                if (!$package_item) {
                    return $this->returnError('404', 'Package Item not Found.');
                    // return response()->json(['result' => false, 'message' => 'Package Item not Found'], 404);
                }
                if (!$package_item->package) {
                    return $this->returnError('404', 'Package not Found.');
                    // return response()->json(['result' => false, 'message' => 'Package Item not Found'], 404);
                }
                // return $package_item->package;
                if ($package_item->package->user_id != auth()->user()->id) {
                    return $this->returnError('404', 'Package Item not Found.');
                    // return response()->json(['result' => false, 'message' => 'Package Item not Found'], 404);
                }


                $package_item->update([
                    'qty' => $quantities[$i]
                ]);

                $i++;
            }

            return response()->json(['result' => true, 'message' => 'Package Updated'], 200);
        } else {
            return response()->json(['result' => false, 'message' => 'Package is Empty'], 200);
        }
    }







    public function addPackageItem(Request $request)
    {
        $validate = Validator($request->all(), [
            'product_id' => "required|integer|exists:products,id",
            'package_id' => "required|integer|exists:packages,id",
            'qty' => "required|integer"
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $item = PackageItem::where('product_id', $request->product_id)->where('package_id', $request->package_id)->first();
        if (isset($item)) {
            if ($item->package->user_id == auth()->user()->id) {
                return response()->json([
                    'result' => false,
                    'message' => 'This item already exists in your package',
                    'item_id' => (int)$item->id,
                    'status' => '301'
                ], 301);
            }
        }

        $package_item = new PackageItem();
        $package_item->product_id = $request->product_id;
        $package_item->package_id = $request->package_id;
        $package_item->qty = $request->qty;
        $package_item->save();

        $package = Package::find($request->package_id);

        $price = 0;
        foreach ($package->package_items as $key => $item) {
            $price += $item->product->unit_price;
        }

        $package->price = $price;
        $package->save();

        // $package_translation = PackageTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'package_id' => $package->id]);
        // $package_translation->name = $request->name;
        // $package_translation->desc = $request->desc;
        // $package_translation->save();

        return response()->json([
            'result' => true,
            'message' => 'Package Item Added',
            'package_item_id' => (int)$package_item->id,
            'package_item' => $package_item,
            'status' => '200'
        ], 200);
    }


    public function removePackageItem($id)
    {


        $item = PackageItem::find($id);

        if (!$item) {
            return $this->returnError('404', 'This item does not exist..!');
        } else {

            $package = Package::find($item->package_id);
            if (!$package) {
                return $this->returnError('404', 'Package not Found.');
            }

            if ($package->user_id == auth()->user()->id) {
                PackageItem::destroy($id);

                $package->price -= $item->product->unit_price;
                $package->save();


                return $this->returnSuccessMessage(translate('Item removed successfuly'), '200');
            } else {
                return $this->returnError('404', 'This item does not exist..!');
            }
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $attribute = Attribute::findOrFail($id);
        if ($request->lang == env("DEFAULT_LANGUAGE")) {
            $attribute->name = $request->name;
        }
        $attribute->save();

        $attribute_translation = AttributeTranslation::firstOrNew(['lang' => $request->lang, 'attribute_id' => $attribute->id]);
        $attribute_translation->name = $request->name;
        $attribute_translation->save();

        flash(translate('Attribute has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {

        $validate = Validator($request->all(), [
            'unit_id' => "required|integer"
        ]);

        if ($validate->fails()) {
            $code = $this->returnCodeAccordingToInput($validate);
            return $this->returnValidationError($code, $validate);
        }

        $unit = Units::find($request->unit_id);
        // return $unit;
        if (!$unit) {

            return response()->json([
                'message' => 'This Unit does not exist..!',
                'is_in_units' => false,
                'unit_id' => (int)$request->unit_id,
                'status' => "200"
            ], 200);
        } else {

            foreach ($unit->unit_translations as $key => $unit_translation) {
                $unit_translation->delete();
            }

            Units::destroy($request->unit_id);

            return response()->json([
                'message' => 'Unit removed successfuly',
                'is_in_units' => true,
                'unit_id' => (int)$request->unit_id,
                'status' => "200"
            ], 200);
        }
    }
}