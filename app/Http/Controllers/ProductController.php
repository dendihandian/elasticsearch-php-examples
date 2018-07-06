<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
    }

    public function index(Request $request)
    {
        // prepare params for searching a document
        $params = [
          'index' => Product::INDEX,
          'type' => Product::TYPE,
          'body' => '{"query" : {"match_all" : {} } }',
        ];

        // searching a document
        $elasticResponse = $this->elasticsearch->search($params);

        // parse source only
        $products = $this->getSources($elasticResponse);

        // prepare response
        $response = [
          'message' => 'Here is your products',
          'data' => $products,
        ];

        return response()->json($response, 200);
    }

    public function show($id)
    {
        // prepare params for getting a document
        $params = [
          'index' => Product::INDEX,
          'type' => Product::TYPE,
          'id' => $id
        ];

        // get a document
        $elasticResponse = $this->elasticsearch->get($params);

        // prepare response
        $response = [
          'message' => 'Here is your product',
          'data' => $elasticResponse['_source'],
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }

    public function store(Request $request)
    {
        $input = $request->all();

        // create product in database
        $product = new Product;
        $product->name = $input['name'];
        $product->slug = str_slug($input['name']);
        $product->stock = $input['stock'];
        $product->price = $input['price'];
        $product->description = $input['description'];
        $product->save();

        // prepare params for creating a document
        $params = [
          'index' => Product::INDEX,
          'type' => Product::TYPE,
          'id' => $product->id,
          'body' => $product->toArray(),
        ];

        // creating a document
        $elasticResponse = $this->elasticsearch->index($params);

        // prepare response
        $response = [
          'message' => 'Successfuly created the product',
          'data' => $product,
          'id' => $product->id,
        ];

        return response()->json($response, 201);
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        // update product in database
        $product = Product::findOrFail($id);
        $product->name = $input['name'];
        $product->slug = str_slug($input['name']);
        $product->stock = $input['stock'];
        $product->price = $input['price'];
        $product->description = $input['description'];
        $product->save();

        // prepare params for updating document
        $params = [
          'index' => Product::INDEX,
          'type' => Product::TYPE,
          'id' => $product->id,
          'body' => [
            'doc'=> $product->toArray(),
          ]
        ];

        // update the document
        $elasticResponse = $this->elasticsearch->update($params);

        // prepare response
        $response = [
          'message' => 'Successfuly updated the product',
          'data' => $product,
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }

    public function destroy($id)
    {
        // get product from id
        $product = Product::findOrFail($id);

        // prepare params for deleting document
        $params = [
          'index' => Product::INDEX,
          'type' => Product::TYPE,
          'id' => $product->id,
        ];

        // delete document
        $elasticResponse = $this->elasticsearch->delete($params);

        // delete product in database
        $product->delete();

        // prepare response
        $response = [
          'message' => 'Successfuly deleted the product',
          'id' => (int) $id,
        ];

        return response()->json($response, 200);
    }

    public function search(Request $request, $query)
    {
        // prepare params for searching a document
        $params = [
          'index' => Product::INDEX,
          'type'  => Product::TYPE,
          'body'  => [
            'query' => [
              'multi_match' => [
                'query' => $query,
                'fields' => ['name', 'description']
              ]
            ]
          ]
        ];

        // searching a document
        $elasticResponse = $this->elasticsearch->search($params);

        // parse source only
        $products = $this->getSources($elasticResponse);

        // prepare response
        $response = [
          'message' => 'Here is your product search results',
          'data' => $products,
          'query' => $query,
        ];

        return response()->json($response, 200);
    }
}
