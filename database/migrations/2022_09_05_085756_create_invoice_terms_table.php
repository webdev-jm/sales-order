<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term_code');
            $table->string('description');
            $table->float('discount')->nullable();
            $table->integer('discount_days')->nullable();
            $table->integer('due_days');
            $table->timestamps();

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
        Schema::dropIfExists('invoice_terms');
    }
}
