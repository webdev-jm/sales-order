<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pafs', function (Blueprint $table) {
            if (!Schema::hasColumn('pafs', 'account_id')) {
                $table->unsignedBigInteger('account_id')->nullable();
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('pafs', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('pafs', 'paf_number')) {
                $table->string('paf_number')->nullable();
            }
            if (!Schema::hasColumn('pafs', 'concept')) {
                $table->string('concept')->nullable();
            }
            if (!Schema::hasColumn('pafs', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('pafs', 'paf_expense_type_id')) {
                $table->unsignedBigInteger('paf_expense_type_id')->nullable();
                $table->foreign('paf_expense_type_id')->references('id')->on('paf_expense_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('pafs', 'paf_support_type_id')) {
                $table->unsignedBigInteger('paf_support_type_id')->nullable();
                $table->foreign('paf_support_type_id')->references('id')->on('paf_support_types')->onDelete('set null');
            }
            if (!Schema::hasColumn('pafs', 'paf_activity_id')) {
                $table->unsignedBigInteger('paf_activity_id')->nullable();
                $table->foreign('paf_activity_id')->references('id')->on('paf_activities')->onDelete('set null');
            }
        });

        Schema::table('paf_details', function (Blueprint $table) {
            if (!Schema::hasColumn('paf_details', 'paf_id')) {
                $table->unsignedBigInteger('paf_id')->nullable();
                $table->foreign('paf_id')->references('id')->on('pafs')->onDelete('cascade');
            }
            if (!Schema::hasColumn('paf_details', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable();
                $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            }
            if (!Schema::hasColumn('paf_details', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable();
            }
            if (!Schema::hasColumn('paf_details', 'branch')) {
                $table->string('branch')->nullable();
            }
            if (!Schema::hasColumn('paf_details', 'type')) {
                $table->string('type')->nullable();
            }
            if (!Schema::hasColumn('paf_details', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('paf_details', 'expense')) {
                $table->decimal('expense', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('paf_details', 'srp')) {
                $table->decimal('srp', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('paf_details', 'percentage')) {
                $table->decimal('percentage', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('paf_details', 'status')) {
                $table->string('status')->nullable();
            }
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
