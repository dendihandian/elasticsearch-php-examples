<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Product;
use Elasticsearch\ClientBuilder;

class ElasticsearchFindProduct
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get id from url parameter
        $id = $request->route()[2]['id'];

        // create elasticsearch client instance
        $elastic = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        try {
            // get the product in elasticsearch by id
            $product = $elastic->get([
              'index' => Product::INDEX,
              'type' => Product::TYPE,
              'id' => $id
            ]);
        } catch (\Exception $e) {
            // get error code & message
            $code = $e->getCode();
            $message = $e->getMessage();

            // change message if product not found (404)
            if ($code === 404) {
              $message = 'Product Not Found';
            }

            // prepare response
            $response = [
              'message' => $message,
              'id' => (int) $id,
            ];

            return response()->json($response, $code);
        }

        // dd($product);

        // add product to request attributes (pass product to the controller)
        $request->attributes->add(['product' => $product["_source"]]);

        return $next($request);
    }
}
