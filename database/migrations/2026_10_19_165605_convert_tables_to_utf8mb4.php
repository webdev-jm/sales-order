<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Converts every table that still stores text as 3-byte utf8 (utf8mb3) to the utf8mb4 set config/database.php uses,
 * so emoji and other 4-byte characters can be saved. Tables already on utf8mb4 are left alone.
 *
 * Each table is rebuilt and locked while it converts, so run this outside office hours on live.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $charset = config('database.connections.mysql.charset', 'utf8mb4');
        $collation = config('database.connections.mysql.collation', 'utf8mb4_general_ci');

        foreach ($this->tablesStillOnUtf8mb3() as $table) {
            DB::statement("ALTER TABLE `{$table}` CONVERT TO CHARACTER SET {$charset} COLLATE {$collation}");
        }
    }

    /**
     * Reverse the migrations.
     *
     * Not reversible: converting back would break any 4-byte character saved since.
     */
    public function down(): void
    {
    }

    /**
     * @return array<int, string>
     */
    private function tablesStillOnUtf8mb3(): array
    {
        return collect(Schema::getTables(DB::getDatabaseName()))
            ->filter(function (array $table) {
                if ($this->isUtf8mb3($table['collation'])) {
                    return true;
                }

                return collect(Schema::getColumns($table['name']))
                    ->contains(fn (array $column) => $this->isUtf8mb3($column['collation']));
            })
            ->pluck('name')
            ->values()
            ->all();
    }

    private function isUtf8mb3(?string $collation): bool
    {
        return $collation !== null && (str_starts_with($collation, 'utf8mb3_') || str_starts_with($collation, 'utf8_'));
    }
};
