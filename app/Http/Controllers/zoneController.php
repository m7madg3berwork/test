<?php

namespace App\Http\Controllers;
use App\Http\Requests\storeZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\City;
use App\Models\ZoneTranslation;
use DB;
use App\Models\Zone;

use Illuminate\Http\Request;

class zoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $zones = new Zone();
        $cities = new City();
        if(isset($request->city_id)) {
            $city_id = $request->city_id;
            $zones = $zones->whereHas('city' ,function($query) use($city_id){
                $query->where('id',$city_id);
            });
            $cities = $cities->where('id', $city_id);
        }
        $zones = $zones->paginate(25);
        $cities = $cities->get();
        return view('backend.setup_configurations.zones.index', compact('zones', 'cities'));
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(storeZoneRequest $request)
    {
        DB::begintransaction();
        try {
            $data = $request->except('_token');
            Zone::create($data);
            DB::commit();
            flash('added new zone')->success();
            return back();
        } catch (\Exiption $e) {
            DB::rollback();
            flash('Unknown error')->error();
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$id)
    {
        $lang  = $request->lang;
        $zone  = Zone::findOrFail($id);
        return view('backend.setup_configurations.zones.edit', compact('zone', 'lang'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateZoneRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            if($request->lang == env("DEFAULT_LANGUAGE")){
                $request->request->add(["name" => $request->name]);
            }
            $data = $request->except("_token","lang","id","_method");
            Zone::updateOrCreate(['id' => $id],$data);
            ZoneTranslation::updateOrCreate(["lang" => $request->lang, "zone_id" => $request->id],[
                'name' => $request->name,
                'zone_id' => $request->id,
            ]);
            DB::commit();
            flash(translate('Zone  has been updated successfully'))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Unknown error')->error();
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
        DB::beginTransaction();
        try {
            Zone::find($id)->delete();
            DB::commit();
            flash(translate('Zone  has been deleted successfully'))->success();
            return back();
        } catch (\Exception $e) {
            DB::rollBack();
            flash('Unknown error')->error();
            return back();
        }
    }
}
