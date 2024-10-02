<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafPrePlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_pre_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('paf_support_type_id')->nullable();
            $table->unsignedBigInteger('paf_activity_id')->nullable();
            $table->string('pre_plan_number');
            $table->integer('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('title');
            $table->string('concept');
            $table->timestamps();

            $table->foreign('paf_id')
                ->references('id')->on('pafs')
                ->onDelete('cascade');

            $table->foreign('account_id')
                ->references('id')->on('accounts')
                ->onDelete('cascade');

            $table->foreign('paf_support_type_id')
                ->references('id')->on('paf_support_types')
                ->onDelete('cascade');

            $table->foreign('paf_activity_id')
                ->references('id')->on('paf_activities')
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
        Schema::dropIfExists('paf_pre_plans');
    }
}
