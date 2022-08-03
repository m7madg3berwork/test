<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
use App\Models\Package;
use App\Models\PackageItem;
use App\Models\PackageTranslation;
use App\Models\Product;
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
        $packages = Package::all();
        return view('backend.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::select('id','name' , 'unit_price')->orderBy('id','DESC')->get();
        return view('backend.packages.create', compact('products'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePackageRequest $request)
    {
        if ($request->has('is_active')) {
            $request->is_active = 1;
        }else{
            $request->is_active = 0;
        }

        $data = [];
        $package = new Package();
        \DB::beginTransaction();
        try {
            $request->request->add(['user_id' => \Auth::id(), 'added_by' => 'admin']);
            $data = $request->except('_token','products');
            $package = $package->create($data);
            $package->products()->sync($request->products);

            $customer_package_translation = PackageTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'package_id' => $package->id]);
            $customer_package_translation->name = $request->name;
            $customer_package_translation->save();

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
    public function edit(Request $request,$id)
    {
        $lang = $request->lang;
        $package = Package::findOrFail($id);
        $products = Product::select('id','name')->orderBy('id','DESC')->get();
        $product_ids = PackageItem::where('package_id',$id)->pluck('product_id')->toArray();
        return view('backend.packages.edit', compact('package', 'lang', 'products','product_ids'));

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
        $data = array();
        $package = new Package();
        \DB::beginTransaction();
        try {
            if ($request->lang == env("DEFAULT_LANGUAGE")) {
                $request->request->add(['name' => $request->name]);
            }
            $data = $request->except("_token","_method","lang","products");
            $package = $package->updateOrCreate(['id' => $id],$data);
            $package->products()->sync($request->products);

            $customer_package_translation = PackageTranslation::firstOrNew(['lang' => $request->lang, 'package_id' => $id]);
            $customer_package_translation->name = $request->name;
            $customer_package_translation->save();
            \DB::commit();
            flash(translate('Package has been updated successfully'))->success();
            return back();
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
        foreach ($package->package_translations as $key => $package_translation) {
            $package_translation->delete();
        }
        Package::destroy($id);

        flash(translate('Package has been deleted successfully'))->success();
        return redirect()->route('packages.index');

    }
}
