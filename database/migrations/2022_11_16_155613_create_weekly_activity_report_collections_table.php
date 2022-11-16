<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_collections', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_id')->nullable();
            $table->string('beginning_ar')->nullable();
            $table->string('due_for_collection')->nullable();
            $table->string('beginning_hanging_balance')->nullable();
            $table->string('target_reconciliations')->nullable();
            $table->string('week_to_date')->nullable();
            $table->string('month_to_date')->nullable();
            $table->string('month_target')->nullable();
            $table->string('balance_to_sell')->nullable();
            $table->timestamps();

            $table->foreign('weekly_activity_report_id')
            ->foreign('id')->on('weekly_activity_reports')
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
        Schema::dropIfExists('weekly_activity_report_collections');
    }
}
