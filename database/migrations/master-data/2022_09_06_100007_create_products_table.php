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
            $table->unsignedBigInteger('brand_id')->unsigned();
            $table->string('stock_code');
            $table->string('description');
            $table->string('size')->nullable();
            $table->decimal('weight', 10,6)->nullable();
            $table->decimal('volume', 10,6)->nullable();
            $table->integer('pallet')->nullable();
            $table->string('category')->nullable();
            $table->string('product_class');
            $table->string('brand');
            $table->string('core_group');
            $table->string('stock_uom');
            $table->string('order_uom');
            $table->string('other_uom');
            $table->integer('order_uom_conversion');
            $table->integer('other_uom_conversion');
            $table->string('order_uom_operator');
            $table->string('other_uom_operator');
            $table->string('status')->nullable();
            $table->boolean('special_product')->default(false);
            $table->string('bar_code')->nullable();
            $table->string('warehouse')->nullable();
            $table->timestamps();

            $table->foreign('brand_id')
                ->references('id')->on('brands')
                ->onDelete('cascade');

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