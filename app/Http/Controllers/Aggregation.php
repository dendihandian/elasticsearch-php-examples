<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

trait Aggregation {
    public function aggs (Request $request)
    {
        $params = [
          'index' => $this->index,
          'type' => $this->type,
          'body' => '{
            "query" : {
              "match_all" : {}
            },
            "aggs": {
              "average_price": {
                "avg": {
                  "field": "price"
                }
              },
              "minimum_price": {
                "min": {
                  "field": "price"
                }
              },
              "maximum_price": {
                "max": {
                  "field": "price"
                }
              }
            }
          }',
        ];

        // searching a document
        $elasticResponse = $this->elasticsearch->search($params);

        // prepare response
        $response = [
          'message' => 'Aggregations',
          'data' => $elasticResponse['aggregations'],
        ];

        return response()->json($response, 200);
    }
}
