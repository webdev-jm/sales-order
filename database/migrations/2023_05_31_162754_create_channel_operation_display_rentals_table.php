<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationDisplayRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_display_rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_id')->nullable();
            $table->string('status');
            $table->string('location');
            $table->decimal('stocks_displayed', 10, 2);
            $table->text('remarks');
            $table->timestamps();

            $table->foreign('channel_operation_id')
                ->references('id')->on('channel_operations')
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
        Schema::dropIfExists('channel_operation_display_rentals');
    }
}
