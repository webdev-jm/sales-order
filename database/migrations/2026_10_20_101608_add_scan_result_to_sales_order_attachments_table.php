<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * What the AI read from a scanned PO, how sure it was, and what the user changed on the review screen,
 * kept with the order's PO attachment.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales_order_attachments', 'scan_result')) {
            return;
        }

        Schema::table('sales_order_attachments', function (Blueprint $table) {
            $table->json('scan_result')->nullable()->after('source');
            $table->unsignedTinyInteger('scan_confidence')->nullable()->after('scan_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_order_attachments', function (Blueprint $table) {
            $table->dropColumn(['scan_result', 'scan_confidence']);
        });
    }
};
