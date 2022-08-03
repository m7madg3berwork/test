<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ZonesCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => (int) $data->id,
                    'city_id' => $data->city_id,
                    'name' => $data->name,
                    'type' => $data->type,
                    'cost' => $data->cost,
                    'customer_cost' => $data->customer_cost,
                    'seller_cost' => $data->seller_cost,
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
