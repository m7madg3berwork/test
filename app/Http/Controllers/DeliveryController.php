<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDelivryRequest;
use App\Http\Requests\UpdateDeliveryRequest;
use App\Models\delivery;
use App\Models\delivery_zones;
use App\Models\DeliveryTranslation;
use App\Models\Zone;
use App\Models\zones;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        $deliveries_queries = delivery::query();
        $deliveries = $deliveries_queries->with('zones')->orderBy('created_at', 'DESC')->paginate(15);
        $zones = zones::all();
        return view('backend.deliveries.index', compact( 'deliveries', 'zones'));
    }

    public function store(StoreDelivryRequest $request) {
        DB::beginTransaction();
        $data = array();
        try {
            $delivery = new delivery();
            $data = $request->except("_token","zone");
            $delivery = $delivery::create($data);
            $delivery->zones()->sync($request->zone);
            DB::commit();
            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('deliveries.index');
        } catch (\Exception $e) {
            DB::rollBack();
            flash(translate($e->getMessage()))->error();
            return back();
        }
    }

    public function edit(Request $request, $id) {
        $lang = $request->lang;
        $delivery = delivery::find($id);
        $zonesSelected = delivery_zones::where('delivery_id',$id)->get();
        $zones = Zone::all();
        return view('backend.deliveries.edit', compact( 'delivery', 'zones', 'zonesSelected', 'lang'));
    }

    public function update(UpdateDeliveryRequest $request, $id) {
        DB::beginTransaction();
        $data = array();
        try {
            $delivery = delivery::find($id);
            $data = $request->except("_token","zone","_method","lang");
            $delivery->update($data);
            $delivery->zones()->sync($request->zone);
            DeliveryTranslation::updateOrCreate(["lang" => $request->lang, "delivery_id" => $id],[
                'name' => $request->name,
                'desc' => $request->desc,
                'delivery_id' => $id,
            ]);

            DB::commit();
            flash(translate('Package has been inserted successfully'))->success();
            return redirect()->route('deliveries.index');
        } catch (\Exception $e) {
            DB::rollBack();
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
        $delivery = delivery::findOrFail($id);

        foreach ($delivery->delivery_translations as $key => $delivery_translation) {
            $delivery_translation->delete();
        }

        $delivery->zones()->detach();

        delivery::destroy($id);
        flash(translate('City has been deleted successfully'))->success();
        return redirect()->route('deliveries.index');
    }

}
