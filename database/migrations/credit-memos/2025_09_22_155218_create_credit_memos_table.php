<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditMemosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_memos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('credit_memo_reason_id')->nullable();
            $table->string('invoice_number');
            $table->string('po_number');
            $table->string('so_number');
            $table->date('cm_date');
            $table->date('ship_date');
            $table->string('ship_code')->nullable();
            $table->string('ship_name')->nullable();
            $table->string('shipping_instruction')->nullable();
            $table->string('ship_address1')->nullable();
            $table->string('ship_address2')->nullable();
            $table->string('ship_address3')->nullable();
            $table->string('ship_address4')->nullable();
            $table->string('ship_address5')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')->on('accounts')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->foreign('credit_memo_reason_id')
                ->references('id')->on('credit_memo_reasons')
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
        Schema::dropIfExists('credit_memos');
    }
}
