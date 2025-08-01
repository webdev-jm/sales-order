<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBrandUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brand_user', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->primary(['brand_id', 'user_id']);

            $table->foreign('brand_id')
                ->references('id')->on('brands')
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
        Schema::dropIfExists('brand_user');
    }
}
