<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\WeekDays;
use DB;
use Illuminate\Http\Request;

class DailyTimeDailvaryController extends Controller
{
    function index(Request $request)
    {
        $sets = DB::table('business_settings')->whereIn('type', ['delivary_days'])->get()->pluck('value');
        $sets[0] = json_decode($sets[0]);

        $days = WeekDays::where('active', 1)->get();

        return response()->json([
            'days' => $days
        ]);
    }


    function getWeekDays()
    {
        // return Carbon::now()->dayOfWeek;


        $days = WeekDays::all();

        return response()->json($days);
    }


    public function store(Request $request)
    {
        $rules = [
            'days' => "required|array",
            'start_time' => "required|date_format:H:i",
            'end_time' => "required|date_format:H:i",
        ];

        $request->validate($rules);


        DB::table('business_settings')->where('type', 'delivary_days')->update([
            'value' => json_encode($request->days)
        ]);

        DB::table('business_settings')->where('type', 'delivary_start_time')->update([
            'value' => $request->start_time
        ]);

        DB::table('business_settings')->where('type', 'delivary_end_time')->update([
            'value' => $request->end_time
        ]);


        flash(translate('Offers daily quantity has been updated successfully'))->success();
        return redirect()->back();
    }
}
