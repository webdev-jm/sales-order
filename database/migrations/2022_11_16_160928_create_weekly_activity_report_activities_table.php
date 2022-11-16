<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_id')->nullable();
            $table->string('activity')->nullable();
            $table->integer('no_of_days_weekly')->nullable();
            $table->integer('no_of_days_mtd')->nullable();
            $table->integer('no_of_days_ytd')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('percent_to_total_working_days')->nullable();
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
        Schema::dropIfExists('weekly_activity_report_activities');
    }
}
