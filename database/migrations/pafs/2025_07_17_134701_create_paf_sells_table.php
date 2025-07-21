<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafSellsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_sells', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_detail_id')->nullable();
            $table->decimal('sellin_amount', 10,7)->nullable();
            $table->decimal('trade_discount1', 10,7)->nullable();
            $table->decimal('trade_discount2', 10,7)->nullable();
            $table->decimal('trade_discount3', 10,7)->nullable();
            $table->decimal('sellin', 10,7)->nullable();
            $table->decimal('sellin_gross', 10,7)->nullable();
            $table->decimal('sellin_net', 10,7)->nullable();
            $table->string('status')->nullable();
            $table->string('price_code')->nullable();
            $table->decimal('srp', 10,7)->nullable();
            $table->timestamps();

            $table->foreign('paf_detail_id')
                ->references('id')->on('paf_details')
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
        Schema::dropIfExists('paf_sells');
    }
}
