<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_login_id')->nullable();
            $table->unsignedBigInteger('shipping_address_id')->nullable();
            $table->string('control_number');
            $table->string('po_number');
            $table->string('paf_number');
            $table->string('reference')->nullable()->comment('sys pro sales reference numbers');
            $table->integer('upload_status')->nullable();
            $table->date('order_date');
            $table->date('ship_date');
            $table->text('shipping_instruction')->nullable();
            $table->string('ship_to_name');
            $table->string('ship_to_building')->nullable();
            $table->string('ship_to_street')->nullable();
            $table->string('ship_to_city')->nullable();
            $table->string('ship_to_postal')->nullable();
            $table->string('status');
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_sales', 15,2)->default(0.00);
            $table->decimal('grand_total', 15,2)->default(0.00);
            $table->decimal('po_value', 15,2)->default(0.00);
            $table->timestamps();

            $table->foreign('account_login_id')
            ->references('id')->on('account_logins')
            ->onDelete('cascade');

            $table->foreign('shipping_address_id')
            ->references('id')->on('shipping_addresses')
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
        Schema::dropIfExists('sales_orders');
    }
}
