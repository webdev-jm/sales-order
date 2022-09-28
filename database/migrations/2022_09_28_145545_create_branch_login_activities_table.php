<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchLoginActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_login_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_login_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->foreign('branch_login_id')
            ->references('id')->on('branch_logins')
            ->onDelete('cascade');

            $table->foreign('activity_id')
            ->references('id')->on('activities')
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
        Schema::dropIfExists('branch_login_activities');
    }
}
