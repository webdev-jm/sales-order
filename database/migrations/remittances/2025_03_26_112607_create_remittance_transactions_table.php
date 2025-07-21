<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemittanceTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remittance_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('remittance_id')->nullable();
            $table->unsignedBigInteger('remittance_reason_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('store_code')->nullable();
            $table->string('store_name')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('description')->nullable();
            $table->string('po_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('invoice_amount')->nullable();
            $table->date('due_date');
            $table->timestamps();

            $table->foreign('remittance_id')
                ->references('id')->on('remittances')
                ->onDelete('cascade');

            $table->foreign('remittance_reason_id')
                ->references('id')->on('remittance_reasons')
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
        Schema::dropIfExists('remittance_transactions');
    }
}
