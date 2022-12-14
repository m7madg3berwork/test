<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Hash;
use Auth;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\DeliveryBoy;
use App\Models\DeliveryHistory;
use App\Models\DeliveryBoyPayment;
use App\Models\DeliveryBoyCollection;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeliveryBoyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $delivery_boys = DeliveryBoy::orderBy('created_at', 'desc')
            ->whereHas("user");

        $sort_search = $request->search;
        if ($sort_search != null) {
            $user_ids = User::where('user_type', 'delivery_boy')->where(function ($user) use ($sort_search) {
                $user->where('name', 'like', '%' . $sort_search . '%')
                    ->orWhere('email', 'like', '%' . $sort_search . '%');
            })->pluck('id')->toArray();
            $delivery_boys = $delivery_boys->where(function ($delivery_boy) use ($user_ids) {
                $delivery_boy->whereIn('user_id', $user_ids);
            });
        }

        $delivery_boys = $delivery_boys->paginate(15);

        return view('delivery_boys.index', compact('delivery_boys', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::where('status', 1)
            ->get();
        return view('delivery_boys.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                   => 'required',
            'phone'                  => 'required',
            'country_id'             => 'required',
            'state_id'               => 'required',
            'delivery_type'          => 'required',
            'national_id'            => 'required',
            'national_id_attachment' => 'required|file|mimes:jpeg,jpg,png,gif|max:10000',
            'national_id_expired'    => 'required',
            'license_id'             => 'required',
            'license_id_attachment'  => 'required|file|mimes:jpeg,jpg,png,gif|max:10000',
            'license_id_expired'     => 'required',
            'license_car'            => 'required',
            'license_car_attachment' => 'required|file|mimes:jpeg,jpg,png,gif|max:10000',
            'license_car_expired'    => 'required',
        ]);

        $data = $request->all();

        if ($request->hasFile('national_id_attachment')) {
            $national_id_attachment = $request->file('national_id_attachment');
            $ex = $national_id_attachment->getClientOriginalExtension();
            $data['national_id_attachment'] = "data:image/$ex;base64," . base64_encode(file_get_contents($national_id_attachment));
        }
        if ($request->hasFile('license_id_attachment')) {
            $license_id_attachment = $request->file('license_id_attachment');
            $ex = $license_id_attachment->getClientOriginalExtension();
            $data['license_id_attachment'] = "data:image/$ex;base64," . base64_encode(file_get_contents($license_id_attachment));
        }
        if ($request->hasFile('license_car_attachment')) {
            $license_car_attachment = $request->file('license_car_attachment');
            $ex = $license_car_attachment->getClientOriginalExtension();
            $data['license_car_attachment'] = "data:image/$ex;base64," . base64_encode(file_get_contents($license_car_attachment));
        }

        $user = User::create($data);

        $delivery_boy = new DeliveryBoy;
        $delivery_boy->user_id = $user->id;
        $delivery_boy->save();

        flash(translate('Delivery Boy has been created successfully'))->success();
        return redirect()->route('delivery-boys.index');
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
    public function edit($id)
    {
        $countries = Country::where('status', 1)->get();
        $states    = State::where('status', 1)->get();
        $delivery_boy = User::findOrFail($id);

        $imageNationalIdAttachment = '';
        if ($delivery_boy->national_id_attachment != null) {
            $national_id_attachment = $delivery_boy->national_id_attachment;
            preg_match("/data:image\/(.*?);/", $national_id_attachment, $image_extension);
            $national_id_attachment = preg_replace('/data:image\/(.*?);base64,/', '', $national_id_attachment);
            $national_id_attachment = str_replace(' ', '+', $national_id_attachment);
            if (count($image_extension) > 1) {
                $imageNationalIdAttachment = 'user' . $delivery_boy->id . '_1.' . $image_extension[1];
                Storage::disk('public')->put($imageNationalIdAttachment, base64_decode($national_id_attachment));
            }
        }
        $imageLicenseIdAttachment = '';
        if ($delivery_boy->license_id_attachment != null) {
            $license_id_attachment = $delivery_boy->license_id_attachment;
            preg_match("/data:image\/(.*?);/", $license_id_attachment, $image_extension);
            $license_id_attachment = preg_replace('/data:image\/(.*?);base64,/', '', $license_id_attachment);
            $license_id_attachment = str_replace(' ', '+', $license_id_attachment);
            if (count($image_extension) > 1) {
                $imageLicenseIdAttachment = 'user' . $delivery_boy->id . '_2.' . $image_extension[1];
                Storage::disk('public')->put($imageLicenseIdAttachment, base64_decode($license_id_attachment));
            }
        }
        $imageLicenseCarAttachment = '';
        if ($delivery_boy->license_car_attachment != null) {
            $license_car_attachment = $delivery_boy->license_car_attachment;
            preg_match("/data:image\/(.*?);/", $license_car_attachment, $image_extension);
            $license_car_attachment = preg_replace('/data:image\/(.*?);base64,/', '', $license_car_attachment);
            $license_car_attachment = str_replace(' ', '+', $license_car_attachment);
            if (count($image_extension) > 1) {
                $imageLicenseCarAttachment = 'user' . $delivery_boy->id . '_3.' . $image_extension[1];
                Storage::disk('public')->put($imageLicenseCarAttachment, base64_decode($license_car_attachment));
            }
        }

        return view('delivery_boys.edit', compact('delivery_boy', 'countries', 'states', 'imageNationalIdAttachment', 'imageLicenseIdAttachment', 'imageLicenseCarAttachment'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $delivery_boy = User::findOrFail($id);

        $request->validate([
            'name'                   => 'required',
            'phone'                  => 'required',
            'country_id'             => 'required',
            'state_id'               => 'required',
            'delivery_type'          => 'required',
            'national_id'            => 'required',
            'national_id_attachment' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:10000',
            'national_id_expired'    => 'required',
            'license_id'             => 'required',
            'license_id_attachment'  => 'nullable|file|mimes:jpeg,jpg,png,gif|max:10000',
            'license_id_expired'     => 'required',
            'license_car'            => 'required',
            'license_car_attachment' => 'nullable|file|mimes:jpeg,jpg,png,gif|max:10000',
            'license_car_expired'    => 'required',
        ]);

        $delivery_boy->name                = $request->name;
        $delivery_boy->phone               = $request->phone;
        $delivery_boy->country_id          = $request->country_id;
        $delivery_boy->state_id            = $request->state_id;
        $delivery_boy->delivery_type       = $request->delivery_type;
        $delivery_boy->national_id         = $request->national_id;
        $delivery_boy->national_id_expired = $request->national_id_expired;
        $delivery_boy->license_id          = $request->license_id;
        $delivery_boy->license_id_expired  = $request->license_id_expired;
        $delivery_boy->license_car         = $request->license_car;
        $delivery_boy->license_car_expired = $request->license_car_expired;

        if ($request->hasFile('national_id_attachment')) {
            $national_id_attachment = $request->file('national_id_attachment');
            $ex = $national_id_attachment->getClientOriginalExtension();
            $delivery_boy->national_id_attachment = "data:image/$ex;base64," . base64_encode(file_get_contents($national_id_attachment));
        }
        if ($request->hasFile('license_id_attachment')) {
            $license_id_attachment = $request->file('license_id_attachment');
            $ex = $license_id_attachment->getClientOriginalExtension();
            $delivery_boy->license_id_attachment = "data:image/$ex;base64," . base64_encode(file_get_contents($license_id_attachment));
        }
        if ($request->hasFile('license_car_attachment')) {
            $license_car_attachment = $request->file('license_car_attachment');
            $ex = $license_car_attachment->getClientOriginalExtension();
            $delivery_boy->license_car_attachment = "data:image/$ex;base64," . base64_encode(file_get_contents($license_car_attachment));
        }

        $delivery_boy->save();

        flash(translate('Delivery Boy has been updated successfully'))->success();
        return redirect()->route('delivery-boys.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function ban($id)
    {
        $delivery_boy = User::findOrFail($id);

        if ($delivery_boy->banned == 1) {
            $delivery_boy->banned = 0;
            flash(translate('Delivery Boy UnBanned Successfully'))->success();
        } else {
            $delivery_boy->banned = 1;
            flash(translate('Delivery Boy Banned Successfully'))->success();
        }

        $delivery_boy->save();

        return back();
    }

    /**
     * Collection form from Delivery boy.
     *
     * @return \Illuminate\Http\Response
     */
    public function order_collection_form(Request $request)
    {
        $delivery_boy_info = DeliveryBoy::with('user')
            ->where('user_id', $request->id)
            ->first();

        return view('delivery_boys.order_collection_form', compact('delivery_boy_info'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function collection_from_delivery_boy(Request $request)
    {
        $delivery_boy = DeliveryBoy::where('user_id', $request->delivery_boy_id)->first();

        if ($request->payout_amount > $delivery_boy->total_collection) {
            flash(translate('Collection Amount Can Not Be Larger Than Collected Amount'))->error();
            return redirect()->route('delivery-boys.index');
        }

        $delivery_boy->total_collection -= $request->payout_amount;

        if ($delivery_boy->save()) {
            $delivery_boy_collection          = new DeliveryBoyCollection;
            $delivery_boy_collection->user_id = $request->delivery_boy_id;
            $delivery_boy_collection->collection_amount = $request->payout_amount;

            $delivery_boy_collection->save();

            flash(translate('Collection From Delivery Boy Successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }

        return redirect()->route('delivery-boys.index');
    }

    /**
     * Paid form for Delivery boy.
     *
     * @return \Illuminate\Http\Response
     */
    public function delivery_earning_form(Request $request)
    {
        $delivery_boy_info = DeliveryBoy::with('user')
            ->where('user_id', $request->id)
            ->first();

        return view('delivery_boys.delivery_earning_form', compact('delivery_boy_info'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function paid_to_delivery_boy(Request $request)
    {
        $delivery_boy = DeliveryBoy::where('user_id', $request->delivery_boy_id)->first();

        if ($request->paid_amount > $delivery_boy->total_earning) {
            flash(translate('Paid Amount Can Not Be Larger Than Payable Amount'))->error();
            return redirect()->route('delivery-boys.index');
        }

        $delivery_boy->total_earning -= $request->paid_amount;

        if ($delivery_boy->save()) {
            $delivery_boy_payment          = new DeliveryBoyPayment;
            $delivery_boy_payment->user_id = $request->delivery_boy_id;
            $delivery_boy_payment->payment = $request->paid_amount;

            $delivery_boy_payment->save();

            flash(translate('Pay To Delivery Boy Successfully'))->success();
        } else {
            flash(translate('Something went wrong'))->error();
        }

        return redirect()->route('delivery-boys.index');
    }

    public function delivery_boys_payment_histories()
    {
        $delivery_boy_payment_query = DeliveryBoyPayment::query();
        if (Auth::user()->user_type == 'delivery_boy') {
            $order_query = $order_query->where('user_id', Auth::user()->id);
        }
        $delivery_boy_payment_query = $delivery_boy_payment_query->paginate(10);

        $delivery_boy_payments = $delivery_boy_payment_query;
        if (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.frontend.cancel_request_list', compact('delivery_boy_payments'));
        }
        return view('delivery_boys.delivery_boys_payment_list', compact('delivery_boy_payments'));
    }

    public function delivery_boys_collection_histories()
    {
        $delivery_boy_collection_query = DeliveryBoyCollection::query();
        if (Auth::user()->user_type == 'delivery_boy') {
            $order_query = $order_query->where('user_id', Auth::user()->id);
        }
        $delivery_boy_collection_query = $delivery_boy_collection_query->paginate(10);

        $delivery_boy_collections = $delivery_boy_collection_query;
        if (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.frontend.cancel_request_list', compact('delivery_boy_collections'));
        }
        return view('delivery_boys.delivery_boys_collection_list', compact('delivery_boy_collections'));
    }

    public function cancel_request_list()
    {
        $order_query = Order::query();
        if (Auth::user()->user_type == 'delivery_boy') {
            $order_query = $order_query->where('assign_delivery_boy', Auth::user()->id);
        }
        $order_query = $order_query->where('delivery_status', '!=', 'cancelled')->where('cancel_request', 1);
        $order_query = $order_query->paginate(10);

        $cancel_requests = $order_query;
        if (Auth::user()->user_type == 'delivery_boy') {
            return view('delivery_boys.frontend.cancel_request_list', compact('cancel_requests'));
        }
        return view('delivery_boys.cancel_request_list', compact('cancel_requests'));
    }

    /**
     * Configuration of delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delivery_boy_configure()
    {
        return view('delivery_boys.delivery_boy_configure');
    }

    public function order_detail($id)
    {
        $order = Order::findOrFail(decrypt($id));
        return view('delivery_boys.frontend.order_detail', compact('order'));
    }

    /**
     * Show the list of assigned delivery by the admin.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assigned_delivery()
    {
        $order_query = Order::query();
        $order_query->where('assign_delivery_boy', Auth::user()->id);
        $order_query->where(function ($order_query) {
            $order_query->where('delivery_status', 'pending')
                ->where('cancel_request', '0');
        })->orWhere(function ($order_query) {
            $order_query->where('delivery_status', 'confirmed')
                ->where('cancel_request', '0');
        });

        $assigned_deliveries = $order_query->paginate(10);

        return view('delivery_boys.frontend.assigned_delivery', compact('assigned_deliveries'));
    }

    /**
     * Show the list of pickup delivery by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pickup_delivery()
    {
        $pickup_deliveries = Order::where('assign_delivery_boy', Auth::user()->id)
            ->where('delivery_status', 'picked_up')
            ->where('cancel_request', '0')
            ->paginate(10);

        return view('delivery_boys.frontend.pickup_delivery', compact('pickup_deliveries'));
    }

    /**
     * Show the list of pickup delivery by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function on_the_way_deliveries()
    {
        $on_the_way_deliveries = Order::where('assign_delivery_boy', Auth::user()->id)
            ->where('delivery_status', 'on_the_way')
            ->where('cancel_request', '0')
            ->paginate(10);

        return view('delivery_boys.frontend.on_the_way_delivery', compact('on_the_way_deliveries'));
    }

    /**
     * Show the list of completed delivery by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function completed_delivery()
    {
        $completed_deliveries = DeliveryHistory::where('delivery_boy_id', Auth::user()->id)
            ->where('delivery_status', 'delivered')
            ->paginate(10);

        return view('delivery_boys.frontend.completed_delivery', compact('completed_deliveries'));
    }

    /**
     * Show the list of pending delivery by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pending_delivery()
    {
        $pending_deliveries = Order::where('assign_delivery_boy', Auth::user()->id)
            ->where('delivery_status', '!=', 'delivered')
            ->where('delivery_status', '!=', 'cancelled')
            ->where('cancel_request', '0')
            ->paginate(10);

        return view('delivery_boys.frontend.pending_delivery', compact('pending_deliveries'));
    }

    /**
     * Show the list of cancelled delivery by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function cancelled_delivery()
    {
        $completed_deliveries = Order::where('assign_delivery_boy', Auth::user()->id)
            ->where('delivery_status', 'cancelled')
            ->paginate(10);

        return view('delivery_boys.frontend.cancelled_delivery', compact('completed_deliveries'));
    }

    /**
     * Show the list of total collection by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function total_collection()
    {
        $today_collections = DeliveryHistory::where('delivery_boy_id', Auth::user()->id)
            ->where('delivery_status', 'delivered')
            ->where('payment_type', 'cash_on_delivery')
            ->paginate(10);

        return view('delivery_boys.frontend.total_collection_list', compact('today_collections'));
    }

    /**
     * Show the list of total earning by the delivery boy.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function total_earning()
    {
        $total_earnings = DeliveryHistory::where('delivery_boy_id', Auth::user()->id)
            ->where('delivery_status', 'delivered')
            ->paginate(10);

        return view('delivery_boys.frontend.total_earning_list', compact('total_earnings'));
    }

    public function cancel_request($order_id)
    {
        $order = Order::findOrFail($order_id);
        $order->cancel_request = '1';
        $order->cancel_request_at = date("Y-m-d H:i:s");
        $order->save();

        return back();
    }

    /**
     * For only delivery boy while changing delivery status.
     * Call from order controller
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store_delivery_history($order)
    {
        $delivery_history = new DeliveryHistory;

        $delivery_history->order_id         = $order->id;
        $delivery_history->delivery_boy_id  = Auth::user()->id;
        $delivery_history->delivery_status  = $order->delivery_status;
        $delivery_history->payment_type     = $order->payment_type;
        if ($order->delivery_status == 'delivered') {
            $delivery_boy = DeliveryBoy::where('user_id', Auth::user()->id)->first();

            if (get_setting('delivery_boy_payment_type') == 'commission') {
                $delivery_history->earning      = get_setting('delivery_boy_commission');
                $delivery_boy->total_earning   += get_setting('delivery_boy_commission');
            }
            if ($order->payment_type == 'cash_on_delivery') {
                $delivery_history->collection    = $order->grand_total;
                $delivery_boy->total_collection += $order->grand_total;

                $order->payment_status           = 'paid';
                if ($order->commission_calculated == 0) {
                    calculateCommissionAffilationClubPoint($order);
                    $order->commission_calculated = 1;
                }
            }

            $delivery_boy->save();
        }
        $order->delivery_history_date = date("Y-m-d H:i:s");

        $order->save();
        $delivery_history->save();
    }
}