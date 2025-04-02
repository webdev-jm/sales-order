<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountUploadTemplateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_upload_template_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_upload_template_id')->nullable();
            $table->unsignedBigInteger('upload_template_field_id')->nullable();
            $table->integer('number')->nullable();
            $table->string('column_name')->nullable();
            $table->integer('column_number')->nullable();
            $table->timestamps();

            $table->foreign('account_upload_template_id', 'account_template_id_foreign')
                ->references('id')->on('account_upload_templates')
                ->onDelete('cascade');

            $table->foreign('upload_template_field_id', 'account_template_field_id_foreign')
                ->references('id')->on('upload_template_fields')
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
        Schema::dropIfExists('account_upload_template_fields');
    }
}
