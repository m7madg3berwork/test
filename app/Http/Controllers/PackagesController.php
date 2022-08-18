<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\PackagesStates;
use App\Models\PackageTranslation;
use App\Models\Product;
use App\Models\State;
use Illuminate\Http\Request;

class PackagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $packages = Package::where("active", "1")->get();
        return view('backend.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::get(['id', 'name', 'unit_price']);
        $newproducts = [];
        foreach ($products as $product) {
            $newproducts[$product->id] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => $product->unit_price
            ];
        }
        $products = $newproducts;
        $productsSort = $products;
        array_multisort(array_column($productsSort, 'name'), SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $productsSort);

        $states = State::where("status", "1")->get()->pluck('name', 'id')->toArray();

        return view('backend.packages.create', compact('products', 'productsSort', 'states'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePackageRequest $request)
    {
        \DB::beginTransaction();
        try {

            $data = $request->except(['states', 'products', 'qty']);
            $data['active']   = ($request->has('active') ? 1 : 0);
            $data['added_by'] = 'admin';
            $data['user_id']  = auth()->user()->id;

            // create package
            $package = Package::create($data);

            // store trans
            PackageTranslation::create([
                'lang'       => app()->getLocale(),
                'package_id' => $package->id,
                'name'       => $request->name,
                'desc'       => $request->desc
            ]);

            // store states
            $states = $request->states;
            if ($states != null) {
                $package->states()->sync($request->states);
            }

            // store products
            $products = $request->products;
            $qty = $request->qty;
            if ($products != null && $qty != null) {
                for ($i = 0; $i < count($products); $i++) {
                    PackageItem::create([
                        'package_id' => $package->id,
                        'product_id' => $products[$i],
                        'qty'        => $qty[$i]
                    ]);
                }
            }

            \DB::commit();
            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('packages.index');
        } catch (\Exception $e) {
            \DB::rollBack();
            flash(translate($e->getMessage()))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->lang;
        $package = Package::findOrFail($id);

        $products = Product::get(['id', 'name', 'unit_price']);
        $newproducts = [];
        foreach ($products as $product) {
            $newproducts[$product->id] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'price' => $product->unit_price
            ];
        }
        $products = $newproducts;
        $productsSort = $products;
        array_multisort(array_column($productsSort, 'name'), SORT_ASC, SORT_NATURAL | SORT_FLAG_CASE, $productsSort);

        $states = State::where("status", "1")->get()->pluck('name', 'id')->toArray();
        $states_ids = PackagesStates::where('package_id', $id)->pluck('state_id')->toArray();

        return view('backend.packages.edit', compact('package', 'lang', 'products', 'productsSort', 'states', 'states_ids'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePackageRequest $request, $id)
    {
        \DB::beginTransaction();
        try {

            $data = $request->except(['states', 'products', 'qty']);
            $data['active'] = ($request->has('active') ? 1 : 0);

            // create package
            $package = Package::findOrFail($id);
            $package->price         = $request->price;
            $package->logo          = $request->logo;
            $package->customer_type = $request->customer_type;
            $package->shipping_type = $request->shipping_type;
            $package->duration      = $request->duration;
            $package->visits_num    = $request->visits_num;
            $package->active        = $request->active;
            $package->save();

            // store trans
            $translation = PackageTranslation::firstOrNew(
                [
                    'lang'       => $request->lang,
                    'package_id' => $package->id
                ]
            );
            $translation->name = $request->name;
            $translation->desc = $request->desc;
            $translation->save();

            // store states
            PackagesStates::where("package_id", $id)->delete();
            $states = $request->states;
            if ($states != null) {
                $package->states()->sync($request->states);
            }

            // store products
            PackageItem::where("package_id", $id)->delete();
            $products = $request->products;
            $qty = $request->qty;
            if ($products != null && $qty != null) {
                for ($i = 0; $i < count($products); $i++) {
                    PackageItem::create([
                        'package_id' => $package->id,
                        'product_id' => $products[$i],
                        'qty'        => $qty[$i]
                    ]);
                }
            }

            \DB::commit();
            flash(translate('Package has been updated successfully'))->success();
            return redirect()->route('packages.index');
        } catch (\Exception $e) {
            \DB::rollBack();
            flash(translate($e->getMessage()))->error();
            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        PackageTranslation::where("package_id", $id)->delete();
        PackagesStates::where("package_id", $id)->delete();
        PackageItem::where("package_id", $id)->delete();

        $package->delete();

        flash(translate('Package has been deleted successfully'))->success();
        return redirect()->route('packages.index');
    }
}