<?php

use Elasticsearch\ClientBuilder;
use App\Models\Product;
use App\Models\Contact;

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

if (! function_exists('generate_contacts')) {
    function generate_contacts($numberOfContacts = 1, $ownerId = null) {
        if ($numberOfContacts > 0) {

            $factoryParams = ($ownerId) ? ['owner_id' => $ownerId] : null;

            // create and get contacts
            $contacts = factory(\App\Models\Contact::class, $numberOfContacts)->create();

            // create elasticsearch client instance
            $elasticsearch = ClientBuilder::create()->build();

            $contacts->each(function ($contact, $key) use ($elasticsearch) {

                $params = [
                  'index' => Contact::INDEX,
                  'type' => Contact::TYPE,
                  'id' => $contact->id,
                  'body' => $contact->toArray(),
                ];

                $response = $elasticsearch->index($params);

            });
        }

        return true;
    }
}
