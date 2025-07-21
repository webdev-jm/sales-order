<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafDetailCancelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_detail_cancels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_detail_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->date('cancel_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('paf_detail_id')
                ->references('id')->on('paf_details')
                ->onDelete('cascade');
            
            $table->foreign('user_id')
                ->references('id')->on('users')
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
        Schema::dropIfExists('paf_detail_cancels');
    }
}
