<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pafs', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('paf_number')->nullable();
            $table->string('concept')->nullable();
            $table->string('status')->nullable();
            $table->unsignedBigInteger('paf_expense_type_id')->nullable();
            $table->unsignedBigInteger('paf_support_type_id')->nullable();
            $table->unsignedBigInteger('paf_activity_id')->nullable();

            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('paf_expense_type_id')->references('id')->on('paf_expense_types')->onDelete('set null');
            $table->foreign('paf_support_type_id')->references('id')->on('paf_support_types')->onDelete('set null');
            $table->foreign('paf_activity_id')->references('id')->on('paf_activities')->onDelete('set null');
        });

        Schema::table('paf_details', function (Blueprint $table) {
            $table->unsignedBigInteger('paf_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('branch')->nullable();
            $table->string('type')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->decimal('expense', 10, 2)->default(0);
            $table->decimal('srp', 10, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->string('status')->nullable();

            $table->foreign('paf_id')->references('id')->on('pafs')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pafs', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['paf_expense_type_id']);
            $table->dropForeign(['paf_support_type_id']);
            $table->dropForeign(['paf_activity_id']);
            $table->dropColumn(['account_id', 'user_id', 'paf_number', 'concept', 'status', 'paf_expense_type_id', 'paf_support_type_id', 'paf_activity_id']);
        });

        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropForeign(['paf_id']);
            $table->dropForeign(['product_id']);
            $table->dropColumn(['paf_id', 'product_id', 'branch_id', 'branch', 'type', 'amount', 'expense', 'srp', 'percentage', 'status']);
        });
    }
};
