<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportObjectivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_objectives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_id')->nullable();
            $table->text('objective');
            $table->timestamps();
            
            $table->foreign('weekly_activity_report_id', 'war_weekly_activity_report_id_foreign')
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
        Schema::dropIfExists('weekly_activity_report_objectives');
    }
}
