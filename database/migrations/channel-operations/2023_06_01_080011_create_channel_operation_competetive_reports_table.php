<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationCompetetiveReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_competetive_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_id')->nullable();
            $table->string('company_name');
            $table->string('product_description');
            $table->string('srp');
            $table->string('type_of_promotion');
            $table->string('impact_to_our_product');
            $table->timestamps();

            $table->foreign('channel_operation_id', 'competetive_reports_channel_operation_id_foreign')
                ->references('id')->on('channel_operations')
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
        Schema::dropIfExists('channel_operation_competetive_reports');
    }
}
