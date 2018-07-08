<?php

use App\Models\Contact;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('owner_id')->unsigned();
            $table->string('first_name', 50)->nullable();
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50);
            $table->string('email', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('address', 254)->nullable();
            $table->timestamps();
        });

        // create  elasticsearch products index and mapping
        $client = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $params = [
          'index' => Contact::INDEX,
          'body' => [
            'settings' => [
              'number_of_shards' => 2,
              'number_of_replicas' => 0
            ],
            'mappings' => [
              Contact::TYPE => [
                '_source' => [
                  'enabled' => true
                ],
                'properties' => [
                  'owner_id' => [
                      'type' => 'integer',
                  ],
                  'first_name' => [
                      'type' => 'text',
                      'analyzer' => 'standard',
                  ],
                  'middle_name' => [
                      'type' => 'text',
                      'analyzer' => 'standard',
                  ],
                  'last_name' => [
                      'type' => 'text',
                      'analyzer' => 'standard',
                  ],
                  'email' => [
                      'type' => 'text',
                      'analyzer' => 'standard',
                  ],
                  'phone' => [
                      'type' => 'text',
                  ],
                  'address' => [
                      'type' => 'text',
                      'analyzer' => 'standard',
                  ],
                ]
              ]
            ]
          ]
        ];

        $response = $client->indices()->create($params);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // drop products elasticsearch index and mapping
        $client = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $deleteParams = [
          'index' => Contact::INDEX
        ];

        $response = $client->indices()->delete($deleteParams);

        Schema::dropIfExists('contacts');
    }
}
