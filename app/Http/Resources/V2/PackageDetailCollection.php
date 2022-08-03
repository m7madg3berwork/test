<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PackageDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            
                    'id' => $this->id,
                    'name' => $this->getTranslation('name'),
                    'desc' => $this->getTranslation('desc'),
                    'user_type' => $this->user_type,
                    'user_id' => $this->user_id,
                    'price' => $this->price,
                    'qty' => $this->qty,
                    'created_at' => $this->created_at,
                    'package_items' => $this->package_items,

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
