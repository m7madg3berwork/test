<?php

namespace App\Services;

use App\Models\Color;
use App\Models\Product;
use App\Models\ProductsZones;
use App\Models\User;
use App\Utility\ProductUtility;
use Combinations;
use Illuminate\Support\Str;

class ProductZonesService
{
    public function store(array $data, $product)
    {
        $collection = collect($data);

        if ($collection['zones']) {
            foreach ($collection['zones'] as $zone) {
                $product_zone = new ProductsZones();
                $product_zone->product_id = $product->id;
                $product_zone->zone_id = $zone['zone_id'];
                $product_zone->cost = $zone['cost'];
                $product_zone->qty = $zone['qty'];
                $product_zone->save();
            }
        }
    }

    public function update(array $data, $product)
    {
        $collection = collect($data);
        ProductsZones::where('product_id',$product->id)->delete();
        if ($collection['zones']) {
            foreach ($collection['zones'] as $zone) {
                $product_zone = new ProductsZones();
                $product_zone->product_id = $product->id;
                $product_zone->zone_id = $zone['zone_id'];
                $product_zone->cost = $zone['cost'];
                $product_zone->qty = $zone['qty'];
                $product_zone->save();
            }
        }
    }

    public function getSelected($product) {
        $productZones = ProductsZones::where('product_id', $product->id)->get();
        return $productZones;
    }
}
