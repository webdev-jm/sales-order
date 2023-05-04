<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityPlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_plan_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_plan_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('work with');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('day');
            $table->date('date');
            $table->text('exact_location')->nullable();
            $table->text('activity')->nullable();
            $table->text('work_with')->nullable();
            $table->timestamps();

            $table->foreign('activity_plan_id')
            ->references('id')->on('activity_plans')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('id')->on('users')
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
        Schema::dropIfExists('activity_plan_details');
    }
}
