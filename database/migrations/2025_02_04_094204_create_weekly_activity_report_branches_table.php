<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_area_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('user_branch_schedule_id')->nullable();
            $table->unsignedBigInteger('branch_login_id')->nullable();
            $table->string('status')->nullable();
            $table->text('action_points')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('weekly_activity_report_area_id', 'war_area_id_foreign')
                ->references('id')->on('weekly_activity_report_areas')
                ->onDelete('cascade');

            $table->foreign('branch_id')
                ->references('id')->on('branches')
                ->onDelete('cascade');

            $table->foreign('user_branch_schedule_id')
                ->references('id')->on('user_branch_schedules')
                ->onDelete('cascade');

            $table->foreign('branch_login_id')
                ->references('id')->on('branch_logins')
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
        Schema::dropIfExists('weekly_activity_report_branches');
    }
}
