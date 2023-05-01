<?php

namespace app\database\migrations;

use Illuminate\Database\Capsule\Manager as Capsule;

class mini_00001_initial
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Capsule::schema()->create('products', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('barcode');
            $table->string('brand_id');
            $table->float('price', 10, 2);
            $table->string('image_url');
            $table->string('date_added');
        });

        Capsule::schema()->create('brands', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Capsule::schema()->create('orders', function ($table) {
            $table->bigIncrements('id');
            $table->text('cart')->nullable();
            $table->text('order_id');
            $table->string('status')->default('pending');
            $table->decimal('total', 10,2)->default(0.00);

            $table->timestamps();
        });

        Capsule::schema()->create('transactions', function ($table) {
            $table->bigIncrements('id');
            $table->text('order_id');
            $table->decimal('total', 10,2)->default(0.00);
            $table->text('payload')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

}