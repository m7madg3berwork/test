<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductMiniCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) use ($request) {
                return [
                    'id'                => $data->id,
                    'name'              => $data->getTranslation('name'),
                    'added_by'          => $data->added_by,
                    'wholesale_product' => $data->wholesale_product == 1 ? 'wholesale' : 'retail',
                    'thumbnail_image'   => uploaded_asset($data->thumbnail_img),
                    'has_discount'      => home_base_price($data, false) != home_discounted_base_price($data, false),
                    'stroked_price'     => home_base_price($data),

                    'main_price'        => format_price(convert_price($data->main_price)),
                    'current_stock'     => $data->qty,

                    'rating'            => (float) $data->rating,
                    'sales'             => (int) $data->num_of_sale,
                    'unit'              => $data->unit,

                    'category' => [
                        'id'            => $data->category != null ? $data->category->id : '',
                        'name'          => $data->category != null ? $data->category->getTranslation('name') : ''
                    ],

                    'brand' => [
                        'id'            => $data->brand != null ? $data->brand->id : '',
                        'brand_name'    => $data->brand != null ? $data->brand->getTranslation('name') : ''
                    ],

                    'links' => [
                        'details'       => route('products.show', $data->id),
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
