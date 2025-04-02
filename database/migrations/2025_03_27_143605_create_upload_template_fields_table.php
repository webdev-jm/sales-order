<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('upload_template_id')->nullable();
            $table->integer('number')->nullable();
            $table->string('column_name')->nullable();
            $table->integer('column_number')->nullable();
            $table->timestamps();

            $table->foreign('upload_template_id')
                ->references('id')->on('upload_templates')
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
        Schema::dropIfExists('upload_template_fields');
    }
}
