<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The live database was built partly outside the migrations, so a plain "php artisan migrate" there has to skip
 * what already exists and then bring the schema in line. These tests run against the migrated test database.
 */
class DatabaseMigrationsTest extends TestCase
{
    private const DDL = '/^\s*(create|alter|drop|rename)\s/i';

    public function test_every_create_table_migration_skips_a_table_that_already_exists(): void
    {
        $offenders = collect(File::allFiles(database_path('migrations')))
            ->filter(fn ($file) => str_contains($file->getContents(), 'Schema::create('))
            ->reject(fn ($file) => str_contains($file->getContents(), 'Schema::hasTable('))
            ->map(fn ($file) => $file->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $offenders, 'Create-table migrations without a Schema::hasTable() check: ' . implode(', ', $offenders));
    }

    public function test_guarded_and_live_sync_migrations_change_nothing_on_a_database_that_is_already_up_to_date(): void
    {
        $migrator = app('migrator');
        $paths = array_merge([database_path('migrations')], glob(database_path('migrations/*'), GLOB_ONLYDIR));
        $statements = [];
        $ran = 0;

        DB::listen(function (QueryExecuted $query) use (&$statements) {
            if (preg_match(self::DDL, $query->sql)) {
                $statements[] = $query->sql;
            }
        });

        foreach ($migrator->getMigrationFiles($paths) as $name => $path) {
            $source = file_get_contents($path);
            $isGuarded = preg_match('/Schema::has(Table|Column)\(/', $source) === 1;
            $isLiveSync = str_contains($name, 'sync_schema_with_live_database') || str_contains($name, 'convert_tables_to_utf8mb4');

            if (!$isGuarded && !$isLiveSync) {
                continue;
            }

            $migration = (fn (string $file) => $this->resolvePath($file))->call($migrator, $path);
            $migration->up();
            $ran++;
        }

        $this->assertGreaterThan(90, $ran);
        $this->assertSame([], $statements);
    }

    public function test_the_schema_has_the_columns_live_had_and_the_ones_live_was_missing(): void
    {
        $expected = [
            'accounts' => ['type', 'warehouse_default', 'warehouse_loose'],
            'activity_plan_detail_trips' => ['company_id'],
            'branches' => ['province', 'city', 'barangay', 'address'],
            'personal_access_tokens' => ['expires_at'],
            'ppuforms' => ['upload_status'],
            'products' => ['brand_tag'],
            'sales_order_product_uoms' => ['warehouse'],
            'shipping_addresses' => ['warehouse'],
            'users' => ['status', 'db_type', 'dark_mode', 'hub_user_id'],
            'weekly_activity_report_approvals' => ['deleted_at'],
        ];

        foreach ($expected as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(Schema::hasColumn($table, $column), "{$table}.{$column} is missing");
            }
        }

        $this->assertFalse(Schema::hasColumn('shipping_addresses', 'wh'));
    }

    public function test_column_definitions_and_indexes_match_live(): void
    {
        $column = fn (string $table, string $name) => collect(Schema::getColumns($table))->firstWhere('name', $name);
        $indexes = fn (string $table) => collect(Schema::getIndexes($table));

        $this->assertSame('decimal(10,5)', $column('price_codes', 'selling_price')['type']);
        $this->assertSame('decimal(15,2)', $column('sales_order_product_uoms', 'uom_total')['type']);
        $this->assertSame('decimal(15,2)', $column('sales_order_product_uoms', 'uom_total_less_disc')['type']);
        $this->assertStringContainsString('unsigned', $column('brands', 'id')['type']);

        foreach ([['ppuforms', 'pickup_date'], ['products', 'brand'], ['sales_orders', 'paf_number'], ['users', 'notify_email'], ['shipping_addresses', 'ship_to_name']] as [$table, $name]) {
            $this->assertTrue($column($table, $name)['nullable'], "{$table}.{$name} should allow empty values as on live");
        }

        $this->assertFalse($indexes('users')->contains(fn (array $index) => $index['columns'] === ['notify_email'] && $index['unique']));
        $this->assertTrue($indexes('users')->contains(fn (array $index) => $index['columns'] === ['hub_user_id'] && $index['unique']));
        $this->assertTrue($indexes('accounts')->contains(fn (array $index) => $index['columns'] === ['company_id', 'account_code'] && $index['unique']));
    }

    public function test_application_tables_store_four_byte_characters(): void
    {
        $utf8mb3 = collect(Schema::getTables(DB::getDatabaseName()))
            ->filter(fn (array $table) => str_starts_with((string) $table['collation'], 'utf8mb3_') || str_starts_with((string) $table['collation'], 'utf8_'))
            ->pluck('name')
            ->all();

        $this->assertSame([], $utf8mb3);
    }

    public function test_two_users_can_share_a_notify_email_as_they_do_on_live(): void
    {
        DB::beginTransaction();

        try {
            User::factory()->create(['notify_email' => 'shared.inbox@example.test']);
            User::factory()->create(['notify_email' => 'shared.inbox@example.test']);

            $this->assertSame(2, User::where('notify_email', 'shared.inbox@example.test')->count());
        } finally {
            DB::rollBack();
        }
    }
}
