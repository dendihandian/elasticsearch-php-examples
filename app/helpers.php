<?php

use Elasticsearch\ClientBuilder;
use App\Models\Product;

if (! function_exists('generate_products')) {
    function generate_products($numberOfProducts = 1) {
        if ($numberOfProducts > 0) {

            // create and get products
            $products = factory(\App\Models\Product::class, $numberOfProducts)->create();

            // create elasticsearch client instance
            $elasticsearch = ClientBuilder::create()->build();

            $products->each(function ($product, $key) use ($elasticsearch) {

                $params = [
                  'index' => Product::INDEX,
                  'type' => 'default',
                  'id' => $product->id,
                  'body' => $product->toArray(),
                ];

                $response = $elasticsearch->index($params);

            });
        }

        return true;
    }
}
