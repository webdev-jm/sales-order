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
            $table->string('lot_number');
            $table->integer('quantity');
            $table->string('uom');
            $table->decimal('unit_price',10,2);
            $table->decimal('amount',10,2);
            $table->date('expiration_date')->nullable();
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
