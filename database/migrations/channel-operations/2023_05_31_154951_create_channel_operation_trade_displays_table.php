<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationTradeDisplaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_trade_displays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_id')->nullable();
            $table->string('planogram');
            $table->string('bevi_pricing');
            $table->integer('osa_bath_actual');
            $table->integer('osa_bath_target');
            $table->integer('osa_face_actual');
            $table->integer('osa_face_target');
            $table->integer('osa_body_actual');
            $table->integer('osa_body_target');
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
        Schema::dropIfExists('channel_operation_trade_displays');
    }
}
