<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_term_id')->nullable();
            $table->unsignedBiginteger('company_id')->nullable();
            $table->unsignedBigInteger('discount_id')->nullable();
            $table->string('account_code');
            $table->string('account_name');
            $table->string('short_name');
            $table->string('price_code')->nullable();
            $table->text('ship_to_address1')->nullable();
            $table->text('ship_to_address2')->nullable();
            $table->text('ship_to_address3')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('tax_number')->nullable();
            $table->boolean('on_hold')->default(false);
            $table->timestamps();

            $table->foreign('invoice_term_id')
            ->references('id')->on('invoice_terms')
            ->onDelete('cascade');

            $table->foreign('company_id')
            ->references('id')->on('companies')
            ->onDelete('cascade');

            $table->foreign('discount_id')
            ->references('id')->on('discounts')
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
        Schema::dropIfExists('accounts');
    }
}
