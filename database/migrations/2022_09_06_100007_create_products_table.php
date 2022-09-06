<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('stock_code');
            $table->string('description');
            $table->string('size')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->string('alternative_code')->nullable();
            $table->string('stock_uom1')->nullable();
            $table->string('stock_uom2')->nullable();
            $table->string('stock_uom3')->nullable();
            $table->double('uom_price1')->nullable();
            $table->double('uom_price2')->nullable();
            $table->double('uom_price3')->nullable();
            $table->timestamps();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}