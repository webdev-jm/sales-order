<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditMemoAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_memo_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credit_memo_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('credit_memo_id')
                ->references('id')->on('credit_memos')
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
        Schema::dropIfExists('credit_memo_attachments');
    }
}
