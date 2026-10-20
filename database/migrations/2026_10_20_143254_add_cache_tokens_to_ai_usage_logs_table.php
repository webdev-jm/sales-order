<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Input tokens the provider read from its prompt cache, and tokens it wrote to it, for each AI call. Both are part
 * of input_tokens; cached reads are billed at a fraction of the input price, which the estimated cost now reflects.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('ai_usage_logs', 'cached_input_tokens')) {
            return;
        }

        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->unsignedInteger('cached_input_tokens')->default(0)->after('input_tokens');
            $table->unsignedInteger('cache_write_tokens')->default(0)->after('cached_input_tokens');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropColumn(['cached_input_tokens', 'cache_write_tokens']);
        });
    }
};
