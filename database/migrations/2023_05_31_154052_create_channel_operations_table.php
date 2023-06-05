<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_login_id')->nullable();
            $table->date('date');
            $table->string('store_in_charge');
            $table->string('position');
            $table->text('total_findings');
            $table->string('status');
            $table->timestamps();

            $table->foreign('branch_login_id')
                ->references('id')->on('branch_logins')
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
        Schema::dropIfExists('channel_operations');
    }
}
