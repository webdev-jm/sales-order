<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBrandUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('brand_user')) {
            return;
        }

        $this->makeBrandIdUnsigned();

        Schema::create('brand_user', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('user_id');

            $table->primary(['brand_id', 'user_id']);

            $table->foreign('brand_id')
                ->references('id')->on('brands')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
            
        });
    }

    /**
     * The live brands table was created with a signed id, which a foreign key from an unsigned column cannot reference.
     *
     * @return void
     */
    private function makeBrandIdUnsigned()
    {
        $id = collect(Schema::getColumns('brands'))->firstWhere('name', 'id');

        if ($id !== null && !str_contains($id['type'], 'unsigned')) {
            DB::statement('ALTER TABLE `brands` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brand_user');
    }
}
