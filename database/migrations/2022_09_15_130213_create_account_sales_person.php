<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountSalesPerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_sales_person', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();

            $table->primary(['sales_person_id', 'account_id']);

            $table->foreign('sales_person_id')
            ->references('id')->on('sales_people')
            ->onDelete('cascade');

            $table->foreign('account_id')
            ->references('id')->on('accounts')
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
        Schema::dropIfExists('account_sales_person');
    }
}
