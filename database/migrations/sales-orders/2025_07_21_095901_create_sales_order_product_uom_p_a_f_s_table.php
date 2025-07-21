<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderProductUomPAFSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_product_uom_p_a_f_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_product_uom_id')->nullable();
            $table->string('paf_number')->nullable();
            $table->string('uom')->nullable();
            $table->integer('quantity')->default(0);
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
        Schema::dropIfExists('sales_order_product_uom_p_a_f_s');
    }
}
