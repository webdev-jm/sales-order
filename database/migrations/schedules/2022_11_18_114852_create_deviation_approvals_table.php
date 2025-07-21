<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviationApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deviation_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deviation_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('deviation_id')
            ->references('id')->on('deviations')
            ->onDelete('cascade');

            $table->foreign('user_id')
            ->references('id')->on('users')
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
        Schema::dropIfExists('deviation_approvals');
    }
}
