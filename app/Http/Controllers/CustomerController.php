<?php

namespace App\Http\Controllers;

use App\Models\UserFunds;
use App\Models\Wallet;
use App\Notifications\SendUserMoneyNotification;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::query()->withCount('orders')
            ->whereHas("customer");
        $users = $users->where('user_type', 'customer');
        if (isset($request->customer_type)) {
            $users = $users->where('customer_type', $request->customer_type);
        }
        $users = $users->orderBy('created_at', 'desc');
        $sort_search = null;
        if ($request->has('search')) {
            $sort_search = $request->search;
            $users = $users->where(function ($q) use ($sort_search) {
                $q->where('name', 'like', '%' . $sort_search . '%')->orWhere('email', 'like', '%' . $sort_search . '%');
            });
        }
        $users = $users->paginate(15);
        return view('backend.customer.customers.index', compact('users', 'sort_search'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users|email',
            'phone' => 'required|unique:users',
        ]);

        $response['status'] = 'Error';

        $user = User::create($request->all());

        $customer = new Customer;

        $customer->user_id = $user->id;
        $customer->save();

        if (isset($user->id)) {
            $html = '';
            $html .= '<option value="">
                        ' . translate("Walk In Customer") . '
                    </option>';
            foreach (Customer::all() as $key => $customer) {
                if ($customer->user) {
                    $html .= '<option value="' . $customer->user->id . '" data-contact="' . $customer->user->email . '">
                                ' . $customer->user->name . '
                            </option>';
                }
            }

            $response['status'] = 'Success';
            $response['html'] = $html;
        }

        echo json_encode($response);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                return view('backend.customer.customers.profile', compact('user'));
            } else {
                flash(translate('User Not Found'))->success();
                return redirect()->back();
            }
        } catch (\Exception $e) {
            flash(translate('Unknown error'))->success();
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        User::destroy($id);
        flash(translate('Customer has been deleted successfully'))->success();
        return redirect()->back();
    }

    public function bulk_customer_delete(Request $request)
    {
        if ($request->id) {
            foreach ($request->id as $customer_id) {
                $this->destroy($customer_id);
            }
        }

        return 1;
    }

    public function login($id)
    {
        $user = User::findOrFail(decrypt($id));

        auth()->login($user, true);

        return redirect()->route('dashboard');
    }

    public function ban($id)
    {
        $user = User::findOrFail(decrypt($id));

        if ($user->banned == 1) {
            $user->banned = 0;
            if ($user->customer_type == 'wholesale') {
                $user->status = 'done';
            }
            flash(translate('Customer Active Successfully'))->success();
        } else {
            $user->banned = 1;
            flash(translate('Customer Blocked Successfully'))->success();
        }

        $user->save();

        return back();
    }

    public function change_customer_status($userId = null)
    {
        try {
            $user = User::find($userId);
            $user->status = 'done';
            $user->save();
            flash(translate('Customer Confirm Successfully'))->success();
            return redirect()->back();
        } catch (\Exception $e) {
            flash(translate('Unknown error'))->error();
            return redirect()->back();
        }
    }

    public function AddToWallet(Request $request)
    {
        $user = new User();
        $user = $user->find($request->user_id);
        $data = array();
        if ($user) {
            if ($request->amount > 0) {
                DB::beginTransaction();
                try {
                    $data = $request->except('_token');
                    UserFunds::create([
                        'user_id' => $user->id,
                        'amount' => $request->amount,
                        'comment' => $request->comment
                    ]);
                    DB::commit();
                    $user_amount = UserFunds::where('user_id', $user->id)->select([DB::raw("SUM(amount) as balance")])->first();
                    $data['balance'] = $user_amount->balance;
                    Notification::send($user, new SendUserMoneyNotification($data));
                    flash(translate('Send Money Successfully'))->success();
                    return redirect()->back();
                } catch (\Exception $e) {
                    DB::rollBack();
                    flash(translate($e->getMessage()))->error();
                    return redirect()->back();
                }
            }
            flash(translate('amount must be bigger than 0'))->error();
            return redirect()->back();
        } else {
            flash(translate('customer not found'))->error();
            return redirect()->back();
        }
    }

    public function AddToWalletBroadCast(Request $request)
    {
        $user = new User();
        $data = array();
        if (count($request['id']) > 0) {
            if ($request['amount'] > 0) {
                foreach ($request['id'] as $id) {
                    $user = $user->find($id);
                    if ($user) {
                        DB::beginTransaction();
                        try {
                            $data = $request->except('_token');
                            UserFunds::create([
                                'user_id' => $user->id,
                                'amount' => $request['amount'],
                                'comment' => $request['comment']
                            ]);
                            DB::commit();
                            $user_amount = UserFunds::where('user_id', $user->id)->select([DB::raw("SUM(amount) as balance")])->first();
                            $data['balance'] = $user_amount->balance;
                            Notification::send($user, new SendUserMoneyNotification($data));
                            flash(translate('Send Money Successfully'))->success();
                        } catch (\Exception $e) {
                            DB::rollBack();
                        }
                    }
                }
                return response()->json('submit successfully  to users', 200);
            } else {
                return response()->json('submit not  successfully ', 500);
            }
        } else {
            return response()->json('submit not  successfully ', 500);
        }
    }
}