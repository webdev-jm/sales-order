<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPlanDetailTripDestinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_plan_detail_trip_destinations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_plan_detail_trip_id')->nullable();
            $table->unsignedBigInteger('activity_plan_detail_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('from');
            $table->string('to')->nullable();
            $table->date('departure');
            $table->date('return')->nullable();
            $table->timestamps();

            $table->foreign('activity_plan_detail_trip_id', 'trip_destination_trip_id_foreign')
                ->references('id')->on('activity_plan_detail_trips')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('activity_plan_detail_trip_destinations');
    }
}
