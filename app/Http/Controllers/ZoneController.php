<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\ZoneTranslation;
use App\Models\City;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sort_zone = $request->sort_zone;
        $sort_city = $request->sort_city;
        $zones_queries = Zone::query();
        if($request->sort_zone) {
            $zones_queries->where('name', 'like', "%$sort_zone%");
        }
        if($request->sort_city) {
            $zones_queries->where('city_id', $request->sort_city);
        }
        $zones = $zones_queries->orderBy('status', 'desc')->paginate(15);
        $cities = City::where('status', 1)->get();

        return view('backend.setup_configurations.zones.index', compact('zones', 'cities', 'sort_zone', 'sort_city'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $zone = new Zone;

        $zone->name = $request->name;
        $zone->cost = $request->cost;
        $zone->city_id = $request->city_id;
        $zone->max_orders = $request->max_orders;

        $zone->save();

        flash(translate('Zone has been inserted successfully'))->success();

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function edit(Request $request, $id)
     {
         $lang  = $request->lang;
         $zone  = Zone::findOrFail($id);
         $cities = City::where('status', 1)->get();
         return view('backend.setup_configurations.zones.edit', compact('zone', 'lang', 'cities'));
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
        $zone = Zone::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
            $zone->name = $request->name;
        }

        $zone->city_id = $request->city_id;
        $zone->cost = $request->cost;
        $zone->max_orders = $request->max_orders;

        $zone->save();

        $zone_translation = ZoneTranslation::firstOrNew(['lang' => $request->lang, 'zone_id' => $zone->id]);
        $zone_translation->name = $request->name;
        $zone_translation->save();

        flash(translate('Zone has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $zone = Zone::findOrFail($id);

        foreach ($zone->zone_translations as $key => $zone_translation) {
            $zone_translation->delete();
        }

        Zone::destroy($id);

        flash(translate('Zone has been deleted successfully'))->success();
        return redirect()->route('zones.index');
    }

    public function updateStatus(Request $request){
        $zone = Zone::findOrFail($request->id);
        $zone->status = $request->status;
        $zone->save();

        return 1;
    }
}
