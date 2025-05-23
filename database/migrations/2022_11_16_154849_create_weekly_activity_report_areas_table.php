<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_id')->nullable();
            $table->date('date');
            $table->string('day');
            $table->text('location')->nullable();
            $table->string('in_base')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('weekly_activity_report_id')
            ->references('id')->on('weekly_activity_reports')
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
        Schema::dropIfExists('weekly_activity_report_areas');
    }
}
