<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;
use DB;

class ProductController extends Controller
{
    use Search, Aggregation;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->elasticsearch = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $this->index = Product::INDEX;
        $this->type = Product::TYPE;
        $this->searchableFields = Product::SEARCHABLE_FIELDS;
    }

    public function index(Request $request)
    {
        // prepare params for searching a document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'body' => '{
            "query" : {
              "match_all" : {}
            },
            "from" : 0,
            "size" : 50
          }',
        ];

        // searching a document
        $elasticResponse = $this->elasticsearch->search($params);

        // parse source only
        $products = $this->getSources($elasticResponse);

        // prepare response
        $response = [
          'message' => 'Product List',
          'data' => $products,
        ];

        return response()->json($response, 200);
    }

    public function show(Request $request, $id)
    {
        // prepare response
        $response = [
          'message' => 'Product Detail',
          'data' => $request->get('product'),
        ];

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        // get all input from request
        $input = $request->all();

        try {
            // perform database transaction
            DB::beginTransaction();

            // create product in database
            $product = Product::create([
              'name' => $input['name'],
              'slug' => str_slug($input['name']),
              'stock' => $input['stock'],
              'price' => $input['price'],
              'description' => $input['description'],
            ]);

            // prepare params for creating a document
            $params = [
              'index' => $this->index,
              'type' => $this->type,
              'id' => $product->id,
              'body' => $product->toArray(),
            ];

            // creating a document
            $elasticResponse = $this->elasticsearch->index($params);

            // commit database transaction
            DB::commit();

        } catch (\Exception $e) {
            // rollback database transaction
            DB::rollback();

            return response()->json([
              'message' => $e->getMessage(),
            ], $e->getCode());
        }

        // prepare response
        $response = [
          'message' => 'Product Created',
          'data' => $product,
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        try {
            // perform database transaction
            DB::beginTransaction();

            // update product in database
            $product = $request->get('product');
            $product->name = $input['name'];
            $product->slug = str_slug($input['name']);
            $product->stock = $input['stock'];
            $product->price = $input['price'];
            $product->description = $input['description'];
            $product->save();

            // prepare params for updating document
            $params = [
              'index' => $this->index,
              'type' => $this->type,
              'id' => $product->id,
              'body' => [
                'doc'=> $product->toArray(),
              ]
            ];

            // update the document
            $elasticResponse = $this->elasticsearch->update($params);

            // commit database transaction
            DB::commit();

        } catch (\Exception $e) {
            // rollback database transaction
            DB::rollback();

            return response()->json([
              'message' => $e->getMessage(),
            ], $e->getCode());
        }

        // prepare response
        $response = [
          'message' => 'Product Updated',
          'data' => $product,
        ];

        return response()->json($response, 200);
    }

    public function destroy(Request $request, $id)
    {
        // get product from id
        $product = $request->get('product');

        // prepare params for deleting document
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'id' => $product->id,
        ];

        try {
          // perform database transaction
          DB::beginTransaction();

          // delete document
          $elasticResponse = $this->elasticsearch->delete($params);

          // delete product in database
          $product->delete();

          // commit database transaction
          DB::commit();

        } catch (\Exception $e) {
            // rollback database transaction
            DB::rollback();

            return response()->json([
              'message' => $e->getMessage(),
            ], $e->getCode());
        }


        // prepare response
        $response = [
          'message' => 'Product Deleted',
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }
}
