<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected $elasticsearch;

    protected function getSources($hits)
    {
        $sources = [];
        foreach ($hits['hits']['hits'] as $hit) {
            array_push($sources, $hit['_source']);
        }

        return $sources;
    }
}
