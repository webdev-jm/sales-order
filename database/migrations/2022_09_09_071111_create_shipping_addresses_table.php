<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippingAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('address_code');
            $table->string('ship_to_name');
            $table->string('building')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('tin')->nullable();
            $table->string('postal')->nullable();
            $table->timestamps();

            $table->foreign('account_id')
            ->references('id')->on('accounts')
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
        Schema::dropIfExists('shipping_addresses');
    }
}
