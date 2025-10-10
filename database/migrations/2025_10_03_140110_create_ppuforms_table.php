<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePpuformsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ppuforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_login_id')->nullable();
            $table->string('control_number');
            $table->integer('upload_status')->nullable();
            $table->date('date_prepared');
            $table->date('date_submitted');
            $table->date('pickup_date');
            $table->string('status');
            $table->integer('total_quantity')->default(0);
            $table->decimal('total_amount', 15,2)->default(0.00);
            $table->timestamps();

            $table->foreign('account_login_id')
            ->references('id')->on('account_logins')
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
        Schema::dropIfExists('ppuforms');
    }
}
