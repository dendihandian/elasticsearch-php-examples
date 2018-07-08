<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait Search {
    public function search(Request $request, $query)
    {
        // prepare params for searching a document
        $params = [
          'index' => $this->index,
          'type'  => $this->type,
          'body'  => [
            'query' => [
              'multi_match' => [
                'query' => $query,
                'fields' => $this->searchableFields,
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
          'message' => 'Here is your search results',
          'data' => $products,
          'query' => $query,
        ];

        return response()->json($response, 200);
    }
}
