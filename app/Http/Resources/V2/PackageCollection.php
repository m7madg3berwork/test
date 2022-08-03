<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id' => $data->id,
                    'name' => $data->getTranslation('name'),
                    'desc' => $data->getTranslation('desc'),
                    'added_by' => $data->added_by,
                    'customer_type' => $data->customer_type,
                    // 'user_id' => $data->user_id,
                    'price' => $data->price,
                    'qty' => $data->qty,
                    'shipping_type' => $data->shipping_type,
                    'duration' => $data->duration,
                    'visits_num' => $data->visits_num,
                    // 'package_shipping_days' => $data->package_shipping_days,
                    'package_items' => $data->package_items,
                    'created_at' => $data->created_at,
                    'links' => [
                        'package' => route('get.admin.package', $data->id)
                    ]
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
