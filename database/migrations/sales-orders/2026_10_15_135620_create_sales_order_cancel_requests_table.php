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
        if (Schema::hasTable('sales_order_cancel_requests')) {
            return;
        }

        Schema::create('sales_order_cancel_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 20)->unique();
            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('user_id');
            $table->text('reason');
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('in_progress_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);

            $table->foreign('sales_order_id')
                ->references('id')->on('sales_orders')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users');
            $table->foreign('processed_by')
                ->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_cancel_requests');
    }
};
