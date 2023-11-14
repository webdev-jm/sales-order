<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->integer('part')->comment('divided into parts based on account limit');
            $table->decimal('total_quantity', 10, 2);
            $table->decimal('total_sales', 15,2);
            $table->timestamps();

            $table->foreign('sales_order_id')
            ->references('id')->on('sales_orders')
            ->onDelete('cascade');

            $table->foreign('product_id')
            ->references('id')->on('products')
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
        Schema::dropIfExists('sales_order_products');
    }
}
