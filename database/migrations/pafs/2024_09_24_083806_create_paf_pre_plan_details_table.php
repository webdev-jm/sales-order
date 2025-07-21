<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafPrePlanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paf_pre_plan_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('paf_pre_plan_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('type');
            $table->string('components');
            $table->string('branch');
            $table->string('stock_code');
            $table->string('description');
            $table->string('price_code');
            $table->string('brand');
            $table->integer('quantity');
            $table->string('GlCode');
            $table->string('IO');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->foreign('paf_pre_plan_id')
                ->references('id')->on('paf_pre_plans')
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
        Schema::dropIfExists('paf_pre_plan_details');
    }
}
