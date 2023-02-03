<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPlanDetailActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_plan_detail_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_plan_detail_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->timestamps();

            $table->foreign('activity_plan_detail_id')
            ->references('id')->on('activity_plan_details')
            ->onDelete('cascade');

            $table->foreign('activity_id')
            ->references('id')->on('activities')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_plan_detail_activities');
    }
}
