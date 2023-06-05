<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePafsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pafs', function (Blueprint $table) {
            $table->id();
            $table->string('PAFNo')->unique();
            $table->string('account_code');
            $table->string('account_name');
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('support_type');
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
        Schema::dropIfExists('pafs');
    }
}
