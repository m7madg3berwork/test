<?php

namespace App\Http\Controllers\Api\V2;

use Illuminate\Http\Request;
use App\Models\Attribute;
use App\Models\Color;
use App\Models\AttributeTranslation;
use App\Models\AttributeValue;
use App\Models\Units;
use App\Models\UnitsTranslation;
use CoreComponentRepository;
use Str;

class UnitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $units = Units::all();

        return response()->json([
            'units' => $units,
            'status' => '200'
        ], 200);
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
    public function add(Request $request)
    {
        $validate = Validator($request->all(), [
            'name' => "required|unique:unit_translations,name",
            // 'lang' => "nullable|string"
        ]);

        if ($validate->fails()) {
            return $validate->errors()->first();
        }

        $unit = new Units;
        $unit->name = $request->name;
        $unit->save();

        $unit_translation = UnitsTranslation::firstOrNew(['lang' => env('DEFAULT_LANGUAGE'), 'unit_id' => $unit->id]);
        $unit_translation->name = $request->name;
        $unit_translation->save();

        return response()->json([
            'message' => translate('Unit added'),
            'unit_id' => (integer)$unit->id,
            'unit' => $unit,
            'status' => '200'
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $lang      = $request->lang;
        $attribute = Attribute::findOrFail($id);
        return view('backend.product.attribute.edit', compact('attribute','lang'));
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
        $attribute = Attribute::findOrFail($id);
        if($request->lang == env("DEFAULT_LANGUAGE")){
          $attribute->name = $request->name;
        }
        $attribute->save();

        $attribute_translation = AttributeTranslation::firstOrNew(['lang' => $request->lang, 'attribute_id' => $attribute->id]);
        $attribute_translation->name = $request->name;
        $attribute_translation->save();

        flash(translate('Attribute has been updated successfully'))->success();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function remove(Request $request)
    {

        $validate = Validator($request->all(), [
            'unit_id' => "required|integer"
        ]);

        if ($validate->fails()) {
            return $validate->errors()->first();
        }

        $unit = Units::find($request->unit_id);
        // return $unit;
        if (!$unit) {

            return response()->json([
                'message' => 'This Unit does not exist..!',
                'is_in_units' => false,
                'unit_id' => (integer)$request->unit_id,
                'status' => "200"
            ], 200);

        } else {

            foreach ($unit->unit_translations as $key => $unit_translation) {
                $unit_translation->delete();
            }
    
            Units::destroy($request->unit_id);

            return response()->json([
                'message' => 'Unit removed successfuly',
                'is_in_units' => true,
                'unit_id' => (integer)$request->unit_id,
                'status' => "200"
            ], 200);

        }

    }






  
    
}
