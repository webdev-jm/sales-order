<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTerritoryBranch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('territory_branch', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('territory_id')->nullable();

            $table->primary(['branch_id', 'territory_id']);

            $table->foreign('branch_id')
            ->references('id')->on('branches')
            ->onDelete('cascade');

            $table->foreign('territory_id')
            ->references('id')->on('territories')
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
        Schema::dropIfExists('territory_branch');
    }
}
