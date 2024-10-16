<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafBrandApprovalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_brand_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('paf_id')
                ->references('id')->on('pafs')
                ->onDelete('cascade');

            $table->foreign('brand_id')
                ->references('id')->on('brands')
                ->omDelete('cascade');

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
        Schema::dropIfExists('paf_brand_approvals');
    }
}
