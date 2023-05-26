<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductivityReportDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productivity_report_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('productivity_report_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('classification_id')->nullable();
            $table->date('date');
            $table->string('salesman')->nullable();
            $table->integer('visited')->default(0);
            $table->decimal('sales', 10, 2)->default(0);
            $table->timestamps();

            $table->foreign('productivity_report_id')
                ->references('id')->on('productivity_reports')
                ->onDelete('cascade');

            $table->foreign('branch_id')
                ->references('id')->on('branches')
                ->onDelete('cascade');

            $table->foreign('classification_id')
                ->references('id')->on('classifications')
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
        Schema::dropIfExists('productivity_report_data');
    }
}
