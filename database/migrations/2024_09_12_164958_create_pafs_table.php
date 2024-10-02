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
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('paf_expense_type_id')->nullable();
            $table->unsignedBigInteger('paf_support_type_id')->nullable();
            $table->unsignedBigInteger('paf_activity_id')->nullable();
            $table->string('paf_number')->unique();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('concept');
            $table->string('status');
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')->on('accounts')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            
            $table->foreign('paf_expense_type_id')
                ->references('id')->on('paf_expense_types')
                ->onDelete('cascade');

            $table->foreign('paf_support_type_id')
                ->references('id')->on('paf_support_types')
                ->onDelete('cascade');

            $table->foreign('paf_activity_id')
                ->references('id')->on('paf_activities')
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
        Schema::dropIfExists('pafs');
    }
}
