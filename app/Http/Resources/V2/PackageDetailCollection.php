<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                return [
                    'id'            => $data->id,
                    'name'          => $data->getTranslation('name'),
                    'desc'          => $data->getTranslation('desc'),
                    'customer_type' => $data->customer_type,
                    'price'         => ceil($data->price),
                    'show_price'    => format_price(convert_price($data->price)),
                    'qty'           => $data->package_items ? $data->package_items->sum('qty') : 0,
                    'shipping_type' => $data->shipping_type,
                    'duration'      => $data->duration,
                    'visits_num'    => $data->visits_num,
                    'states'        => $data->states,
                    'products'      => $data->products
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'result' => true
        ];
    }
}