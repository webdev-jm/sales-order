<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationExtraDisplaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_extra_displays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_id')->nullable();
            $table->string('location');
            $table->decimal('rate_per_month', 10, 2);
            $table->decimal('amount', 10, 2);
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
        Schema::dropIfExists('channel_operation_extra_displays');
    }
}
