<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageRequest extends FormRequest
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
            'name' => ['required','string'],
            'qty' => ['required','integer'],
            'price' => ['required','regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'customer_type' => ['required', Rule::in('wholesale','retail')],
            'shipping_type' => ['required', Rule::in('weekly','monthly')],
            'duration' => ['required','integer'],
            'visits_num' => ['required','integer'],
            'products' => ['sometimes','array'],
            'products.*' => ['required','exists:products,id'],
            'desc' => ['required'],
        ];
    }
}
