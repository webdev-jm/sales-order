<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviationSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deviation_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deviation_id')->nullable();
            $table->unsignedBigInteger('user_branch_schedule_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('date');
            $table->text('activity');
            $table->string('type');
            $table->timestamps();

            $table->foreign('deviation_id')
            ->references('id')->on('deviations')
            ->onDelete('cascade');

            $table->foreign('user_branch_schedule_id')
            ->references('id')->on('user_branch_schedules')
            ->onDelete('cascade');

            $table->foreign('branch_id')
            ->references('id')->on('branches')
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
        Schema::dropIfExists('deviation_schedules');
    }
}
