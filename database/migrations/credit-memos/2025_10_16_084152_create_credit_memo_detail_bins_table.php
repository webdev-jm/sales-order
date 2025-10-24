<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditMemoDetailBinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_memo_detail_bins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_memo_detail_id')->nullable();
            $table->string('lot_number')->nullable();
            $table->string('bin_location')->nullable();
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('uom')->nullable();
            $table->timestamps();

            $table->foreign('credit_memo_detail_id')
                ->references('id')->on('credit_memo_details')
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
        Schema::dropIfExists('credit_memo_detail_bins');
    }
}
