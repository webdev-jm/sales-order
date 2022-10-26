<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePriceCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('code');
            $table->decimal('selling_price', 10, 3);
            $table->string('price_basis');
            $table->timestamps();

            $table->foreign('company_id')
            ->references('id')->on('companies')
            ->onDelete('cascade');

            $table->foreign('product_id')
            ->references('id')->on('products')
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
        Schema::dropIfExists('price_codes');
    }
}
