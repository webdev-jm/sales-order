<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpuformItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppuform_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppuform_id')->nullable();
            $table->string('rtv_number');
            $table->date('rtv_date');
            $table->string('branch_name');
            $table->decimal('total_quantity', 10, 2);
            $table->decimal('total_amount', 15,2);
            $table->string('remarks');

            $table->timestamps();

            $table->foreign('ppuform_id')
            ->references('id')->on('ppuforms')
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
        Schema::dropIfExists('ppuform_items');
    }
}
