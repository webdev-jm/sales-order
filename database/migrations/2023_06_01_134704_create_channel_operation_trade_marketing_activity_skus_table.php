<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChannelOperationTradeMarketingActivitySkusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('channel_operation_trade_marketing_activity_skus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('channel_operation_trade_marketing_activity_id')->nullable();
            $table->unsignedBigInteger('paf_detail_id')->nullable();
            $table->string('sku_code');
            $table->string('sku_description');
            $table->string('brand');
            $table->decimal('actual', 10,2);
            $table->decimal('target_maxcap', 10,2);
            $table->timestamps();

            $table->foreign('channel_operation_trade_marketing_activity_id', 'trade_marketing_activity_skus_operation_id_foreign')
                ->references('id')->on('channel_operation_trade_marketing_activities')
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
        Schema::dropIfExists('channel_operation_trade_marketing_activity_skus');
    }
}
