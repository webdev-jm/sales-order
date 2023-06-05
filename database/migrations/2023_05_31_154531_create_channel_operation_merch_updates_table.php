<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationMerchUpdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_merch_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_id')->nullable();
            $table->string('status');
            $table->integer('actual');
            $table->integer('target');
            $table->integer('days_of_gaps');
            $table->decimal('sales_opportunities', 10, 2);
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
        Schema::dropIfExists('channel_operation_merch_updates');
    }
}
