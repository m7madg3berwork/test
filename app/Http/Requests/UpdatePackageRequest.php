<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePackageRequest extends FormRequest
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
            'name' => ['required', 'string'],
            'desc' => ['required'],
            'price' => ['required', 'regex:/^[0-9]+(\.[0-9][0-9]?)?$/'],
            'customer_type' => ['required', Rule::in('wholesale', 'retail')],
            'shipping_type' => ['required', Rule::in('weekly', 'monthly')],
            'duration' => ['required', 'integer'],
            'visits_num' => ['required', 'integer'],
            'products' => ['required', 'array'],
            'qty' => ['required', 'array'],
            'states' => ['required', 'array'],
            'states.*' => ['required', 'exists:states,id'],
        ];
    }
}