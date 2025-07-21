<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPlanDetailTripAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_plan_detail_trip_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_plan_detail_trip_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('url');
            $table->timestamps();

            $table->foreign('activity_plan_detail_trip_id', 'trip_id_foreign')
                ->references('id')->on('activity_plan_detail_trips')
                ->onDelete('cascade');

            $table->SoftDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_plan_detail_trip_attachments');
    }
}
