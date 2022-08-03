<?php

namespace App\Http\Controllers;

use App\Http\Requests\DailyOrderDayNumberRequest;
use App\Models\DailyOrderNumber;
use Illuminate\Http\Request;

class DailyOrderController extends Controller
{
    public function create(Request $request)
    {
        try {
            if (!DailyOrderNumber::where('id',1)->first()) {
                DailyOrderNumber::updateOrCreate(['id' => 1], [
                    'seller' => 0,
                    'customer' => 0,
                ]);
            }
            // get DailyOrderNumber
            $dailyOrderNumber = DailyOrderNumber::find(1);
            return view('backend.daily_order.create',compact('dailyOrderNumber'));
        } catch (\Exception $e) {
            dd($e);
            flash('Unknown error')->error();
            return back();
        }
    }

    public function store(DailyOrderDayNumberRequest $request) {
        \DB::beginTransaction();
        try {
            $data = $request->except("_token","button");
            DailyOrderNumber::updateOrCreate(['id' => 1], $data);
            \DB::commit();
            flash('updated order day number')->success();
            return back();
        } catch (\Exception $e) {
            \DB::rollBack();
            flash($e->getMessage())->error();
            return back();
        }
    }
}
