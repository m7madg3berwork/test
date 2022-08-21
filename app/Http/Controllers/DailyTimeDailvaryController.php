<?php

namespace App\Http\Controllers;

use App\Models\WeekDays;
use DB;
use Illuminate\Http\Request;

class DailyTimeDailvaryController extends Controller
{
    function create(Request $request)
    {
        $days = WeekDays::all();

        // $sets =  DB::table('business_settings')->whereIn('type' , ['delivary_days','delivary_start_time','delivary_end_time'])->get()->pluck('value');
        // $sets[0] = json_decode($sets[0]);
        // return json_decode($sets[0]);

        return view('backend.days_time_delivary.create', compact(['days']));
    }

    public function store(Request $request)
    {
        $rules = [
            'days' => "required|array",
            'start_time' => "required|array",
            'end_time' => "required|array",
            'order_count_customer' => 'nullable',
            'order_count_wholesale' => 'nullable',
        ];

        $request->validate($rules);

        $days = WeekDays::query()->get()->pluck('id');

        // return $request->start_time[0];
        // return $request->end_time[1];
        // return $days[0];
        // return $request->days;

        for ($i = 0; $i < 7; $i++) {
            WeekDays::where('id', $days[$i])
                ->update([
                    'start_time' => $request->start_time[$i],
                    'end_time' => $request->end_time[$i],
                    'order_count_customer' => $request->order_count_customer[$i],
                    'order_count_wholesale' => $request->order_count_wholesale[$i],
                ]);
        }

        for ($i = 0; $i < 7; $i++) {
            if (in_array($i, $request->days)) {
                WeekDays::where('id', $i)
                    ->update([
                        'active' => 1
                    ]);
            } else {
                WeekDays::where('id', $i)
                    ->update([
                        'active' => 0
                    ]);
            }
        }


        flash(translate('Delivary\'s Days & Times have been updated successfully'))->success();
        return redirect()->back();
    }
}