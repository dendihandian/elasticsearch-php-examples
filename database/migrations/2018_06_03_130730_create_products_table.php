<?php

use App\Models\Product;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->integer('stock')->unsigned()->default(1);
            $table->integer('price')->unsigned()->default(1);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // create  elasticsearch products index and mapping
        $client = ClientBuilder::create()
          ->setHosts([env('ELASTICSEARCH_HOST')])
          ->build();

        $params = [
          'index' => Product::INDEX,
          'body' => [
            'settings' => [
              'number_of_shards' => 2,
              'number_of_replicas' => 0
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
          'index' => Product::INDEX
        ];

        $response = $client->indices()->delete($deleteParams);

        Schema::dropIfExists('products');
    }
}
