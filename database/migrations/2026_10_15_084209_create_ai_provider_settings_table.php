<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_provider_settings')) {
            return;
        }

        Schema::create('ai_provider_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->string('model')->nullable();
            $table->string('effort', 20)->nullable();
            $table->string('base_url')->nullable();
            $table->decimal('input_price', 12, 4)->nullable();
            $table->decimal('output_price', 12, 4)->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_provider_settings');
    }
};
