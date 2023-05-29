<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesmenLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salesmen_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salesman_id')->nullable();
            $table->string('province');
            $table->string('city');
            $table->timestamps();

            $table->foreign('salesman_id')
                ->references('id')->on('salesmen')
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
        Schema::dropIfExists('salesmen_locations');
    }
}
