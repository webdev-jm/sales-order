<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyActivityReportAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weekly_activity_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weekly_activity_report_branch_id')->nullable();
            $table->string('title');
            $table->text('file');
            $table->timestamps();

            $table->foreign('weekly_activity_report_branch_id', 'war_attachments_war_branch_id_foreign')
                ->references('id')->on('weekly_activity_report_branches')
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
        Schema::dropIfExists('weekly_activity_report_attachments');
    }
}
