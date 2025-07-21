<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branch_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->nullable();
            $table->string('region')->nullable();
            $table->string('classification')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('account_group')->nullable();
            $table->string('inventory')->nullable();
            $table->string('type')->nullable();
            $table->string('area_code')->nullable();
            $table->string('area_name')->nullable();
            $table->string('classification_code')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branch_uploads');
    }
}
