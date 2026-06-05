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
        Schema::table('activity_plan_details', function (Blueprint $table) {
            $table->text('on_leave_remarks')->nullable()->after('is_on_leave');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_plan_details', function (Blueprint $table) {
            $table->dropColumn('on_leave_remarks');
        });
    }
};
