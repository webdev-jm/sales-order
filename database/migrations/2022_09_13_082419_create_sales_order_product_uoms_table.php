<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderProductUomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_product_uoms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_product_id')->nullable();
            $table->string('uom');
            $table->decimal('quantity', 10, 2);
            $table->decimal('uom_total', 10, 2);
            $table->decimal('uom_total_less_disc', 10, 2);
            $table->string('warehouse')->nullable();
            $table->timestamps();

            $table->foreign('sales_order_product_id')
                ->references('id')->on('sales_order_products')
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
        Schema::dropIfExists('sales_order_product_uoms');
    }
}
