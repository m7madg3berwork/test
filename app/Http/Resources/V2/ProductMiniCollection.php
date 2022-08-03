<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) use($request) {
                if($request->header('auth')){
                    return [
                        'id' => $data->id,
                        'name' => $data->getTranslation('name'),
                        'added_by' => $data->added_by,
                        'wholesale_product' => $data->wholesale_product==0 ? 'wholesale' : 'retail',
                        'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                        'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false) ,
                        'stroked_price' => home_base_price($data),
                        'main_price' => format_price(convert_price($data['zone_price'])),
                        'zone_qty' => $data['zone_qty'],
                        // 'zone_price' => $data->zone_price,
                        'rating' => (double) $data->rating,
                        'sales' => (integer) $data->num_of_sale,
                        'unit' => $data->unit,
                        'return_policy' => $data->return_policy,
                        'zones' => $data->zones()->pluck('zone_id')->toArray(),
                        'category' => [
                            'id' => $data->category->id,
                            'name' => $data->category->getTranslation('name')
                        ],
                        'brand' => [
                            'id' => $data->brand->id,
                            'brand_name' => $data->brand->getTranslation('name')
                        ],
                        'links' => [
                            'details' => route('products.show', $data->id),
                        ]
                    ];
                }else {
                    return [
                        'id' => $data->id,
                        'name' => $data->getTranslation('name'),
                        'added_by' => $data->added_by,
                        'wholesale_product' => $data->wholesale_product==0 ? 'wholesale' : 'retail',
                        'thumbnail_image' => uploaded_asset($data->thumbnail_img),
                        'has_discount' => home_base_price($data, false) != home_discounted_base_price($data, false) ,
                        'stroked_price' => home_base_price($data),
                        'main_price' => home_discounted_base_price($data),
                        'rating' => (double) $data->rating,
                        'sales' => (integer) $data->num_of_sale,
                        'unit' => $data->unit,
                        'return_policy' => $data->return_policy,
                        'zones' => $data->zones()->pluck('zone_id')->toArray(),
                        'category' => [
                            'id' => $data->category->id,
                            'name' => $data->category->getTranslation('name')
                        ],
                        'brand' => [
                            'id' => $data->brand->id,
                            'brand_name' => $data->brand->getTranslation('name')
                        ],
                        'links' => [
                            'details' => route('products.show', $data->id),
                        ]
                    ];
                }
                
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
