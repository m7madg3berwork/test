<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDelivryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required'],
            'zone' => ['sometimes','array'],
            'zone.*' => ['sometimes','array'],
            'zone.*.zone_id' => ['required',Rule::exists('zones','id')],
            'zone.*.cost' => ['required','numeric'],
            'zone.*.type' => ['required',Rule::in('wholesale','retail')],
        ];
    }
}
