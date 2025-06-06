<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('notify_email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('group_code')->nullable();
            $table->string('status')->default('active')->nullable();
            $table->boolean('coe')->default(false);
            $table->string('db_type')->nullable();
            $table->rememberToken();
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            $table->foreign('department_id')
                ->references('id')->on('departments')
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
        Schema::dropIfExists('users');
    }
}
