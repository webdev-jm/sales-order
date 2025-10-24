<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditMemoDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_memo_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_memo_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('credit_note_number')->nullable();
            $table->string('warehouse')->nullable();
            $table->integer('order_quantity')->nullable();
            $table->string('order_uom')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_uom')->nullable();
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->integer('ship_quantity')->nullable();
            $table->integer('stock_quantity_to_ship')->nullable();
            $table->string('stocking_uom')->nullable();
            $table->timestamps();

            $table->foreign('credit_memo_id')
                ->references('id')->on('credit_memos')
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
        Schema::dropIfExists('credit_memo_details');
    }
}
