<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPlanDetailTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_plan_detail_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_plan_detail_id')->nullable();
            $table->string('trip_number')->unique();
            $table->string('departure');
            $table->string('arrival');
            $table->string('reference_number');
            $table->string('transportation_type');
            $table->string('attachment_path')->nullable();
            $table->timestamps();

            $table->foreign('activity_plan_detail_id')
                ->references('id')->on('activity_plan_details')
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
        Schema::dropIfExists('activity_plan_detail_trips');
    }
}
