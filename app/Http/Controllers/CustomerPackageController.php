<?php

namespace App\Http\Controllers;

use App\Models\CustomerPackageProducts;
use App\Models\Product;
use App\Utility\PayfastUtility;
use Illuminate\Http\Request;
use App\Models\CustomerPackage;
use App\Models\CustomerPackageTranslation;
use App\Models\CustomerPackagePayment;
use Auth;
use Session;
use App\Models\User;
use App\Http\Controllers\PublicSslCommerzPaymentController;
use App\Http\Controllers\InstamojoController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\VoguePayController;
use App\Utility\PayhereUtility;

class CustomerPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $customer_packages = CustomerPackage::all();
        return view('backend.customer.customer_packages.index', compact('customer_packages'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products = Product::select('id','name')->orderBy('id','DESC')->get();
        return view('backend.customer.customer_packages.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [];
        $cusomerPack = new CustomerPackage();
        \DB::beginTransaction();
        try {
            $data = $request->except('_token','products');
            $cusomerPack = $cusomerPack->create($data);
            $cusomerPack->products()->sync($request->products);

            $customer_package_translation = CustomerPackageTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'customer_package_id' => $cusomerPack->id]);
            $customer_package_translation->name = $request->name;
            $customer_package_translation->save();

            \DB::commit();

            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('customer_packages.index');
        } catch (\Exception $e) {
            dd($e);
            \DB::rollBack();
            flash(translate($e->getMessage()))->error();
            return back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang = $request->lang;
        $customer_package = CustomerPackage::findOrFail($id);
        $products = Product::select('id','name')->orderBy('id','DESC')->get();
        $product_ids = CustomerPackageProducts::where('customer_package_id',$id)->pluck('product_id')->toArray();
        return view('backend.customer.customer_packages.edit', compact('customer_package', 'lang', 'products','product_ids'));
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
        $data = array();
        $customer_package = new CustomerPackage();
        \DB::beginTransaction();
        try {
            if ($request->lang == env("DEFAULT_LANGUAGE")) {
                $request->request->add(['name' => $request->name]);
            }
            $data = $request->except("_token","_method","lang","products");
            $customer_package = $customer_package->updateOrCreate(['id' => $id],$data);
            $customer_package->products()->sync($request->products);

            $customer_package_translation = CustomerPackageTranslation::firstOrNew(['lang' => $request->lang, 'customer_package_id' => $id]);
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer_package = CustomerPackage::findOrFail($id);
        foreach ($customer_package->customer_package_translations as $key => $customer_package_translation) {
            $customer_package_translation->delete();
        }
        CustomerPackage::destroy($id);

        flash(translate('Package has been deleted successfully'))->success();
        return redirect()->route('customer_packages.index');
    }

    public function purchase_package(Request $request)
    {
        $data['customer_package_id'] = $request->customer_package_id;
        $data['payment_method'] = $request->payment_option;

        $request->session()->put('payment_type', 'customer_package_payment');
        $request->session()->put('payment_data', $data);

        $customer_package = CustomerPackage::findOrFail(Session::get('payment_data')['customer_package_id']);

        if ($customer_package->amount == 0) {
            $user = User::findOrFail(Auth::user()->id);
            if ($user->customer_package_id != $customer_package->id) {
                return $this->purchase_payment_done(Session::get('payment_data'), null);
            } else {
                flash(translate('You can not purchase this package anymore.'))->warning();
                return back();
            }
        }

        $decorator = __NAMESPACE__ . '\\Payment\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $request->payment_option))) . "Controller";
        if (class_exists($decorator)) {
            return (new $decorator)->pay($request);
        }
    }

    public function purchase_payment_done($payment_data, $payment)
    {
        $user = User::findOrFail(Auth::user()->id);
        $user->customer_package_id = $payment_data['customer_package_id'];
        $customer_package = CustomerPackage::findOrFail($payment_data['customer_package_id']);
        $user->remaining_uploads += $customer_package->product_upload;
        $user->save();

        flash(translate('Package purchasing successful'))->success();
        return redirect()->route('dashboard');
    }

    public function purchase_package_offline(Request $request)
    {
        $customer_package = new CustomerPackagePayment;
        $customer_package->user_id = Auth::user()->id;
        $customer_package->customer_package_id = $request->package_id;
        $customer_package->payment_method = $request->payment_option;
        $customer_package->payment_details = $request->trx_id;
        $customer_package->approval = 0;
        $customer_package->offline_payment = 1;
        $customer_package->reciept = ($request->photo == null) ? '' : $request->photo;
        $customer_package->save();
        flash(translate('Offline payment has been done. Please wait for response.'))->success();
        return redirect()->route('customer_products.index');
    }
}
