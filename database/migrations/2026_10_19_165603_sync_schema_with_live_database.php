<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings every database to the same shape as the live one.
 *
 * The live database was built partly outside the migrations, so it lacks columns that later edits put into old
 * create-table migrations, and it has columns, types and nullable settings that the migrations never had.
 * Every step checks the current schema first, so it only changes what differs: on live it adds the missing
 * columns, on a fresh install it adds what only live had, and running it on either is safe.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->addColumnsLiveIsMissing();
        $this->addColumnsOnlyLiveHad();
        $this->alignColumnDefinitionsWithLive();
        $this->alignIndexesWithLive();
    }

    /**
     * Reverse the migrations.
     *
     * Not reversible: the columns hold live data and the definitions match what live already had.
     */
    public function down(): void
    {
    }

    /**
     * Columns the create-table migrations define that were never added to live.
     */
    private function addColumnsLiveIsMissing(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'province')) {
                $table->string('province')->nullable()->after('branch_name');
            }
            if (!Schema::hasColumn('branches', 'city')) {
                $table->string('city')->nullable()->after('province');
            }
            if (!Schema::hasColumn('branches', 'barangay')) {
                $table->string('barangay')->nullable()->after('city');
            }
            if (!Schema::hasColumn('branches', 'address')) {
                $table->text('address')->nullable()->after('barangay');
            }
        });

        if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                $table->timestamp('expires_at')->nullable()->after('last_used_at');
            });
        }

        if (!Schema::hasColumn('ppuforms', 'upload_status')) {
            Schema::table('ppuforms', function (Blueprint $table) {
                $table->integer('upload_status')->nullable()->after('control_number');
            });
        }

        if (!Schema::hasColumn('sales_order_product_uoms', 'warehouse')) {
            Schema::table('sales_order_product_uoms', function (Blueprint $table) {
                $table->string('warehouse')->nullable()->after('uom_total_less_disc');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->nullable()->after('group_code');
            }
            if (!Schema::hasColumn('users', 'db_type')) {
                $table->string('db_type')->nullable()->after('coe');
            }
        });
    }

    /**
     * Columns live has that no migration created, so fresh installs and test databases get them too.
     */
    private function addColumnsOnlyLiveHad(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'type')) {
                $table->string('type')->nullable()->after('sales_order_uom');
            }
            if (!Schema::hasColumn('accounts', 'warehouse_default')) {
                $table->string('warehouse_default')->nullable()->after('po_prefix');
            }
            if (!Schema::hasColumn('accounts', 'warehouse_loose')) {
                $table->string('warehouse_loose')->nullable()->after('warehouse_default');
            }
        });

        if (!Schema::hasColumn('activity_plan_detail_trips', 'company_id')) {
            Schema::table('activity_plan_detail_trips', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->after('user_id');
            });
        }

        if (!Schema::hasColumn('products', 'brand_tag')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('brand_tag')->nullable()->after('brand');
            });
        }

        if (!Schema::hasColumn('users', 'dark_mode')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('dark_mode')->default(false)->after('deleted_at');
            });
        }

        if (!Schema::hasColumn('weekly_activity_report_approvals', 'deleted_at')) {
            Schema::table('weekly_activity_report_approvals', function (Blueprint $table) {
                $table->softDeletes()->after('updated_at');
            });
        }

        if (Schema::hasColumn('shipping_addresses', 'wh') && !Schema::hasColumn('shipping_addresses', 'warehouse')) {
            Schema::table('shipping_addresses', function (Blueprint $table) {
                $table->renameColumn('wh', 'warehouse');
            });
        } elseif (!Schema::hasColumn('shipping_addresses', 'warehouse')) {
            Schema::table('shipping_addresses', function (Blueprint $table) {
                $table->string('warehouse')->nullable()->after('active');
            });
        }
    }

    /**
     * Live stores more decimals and allows empty values where the migrations did not.
     */
    private function alignColumnDefinitionsWithLive(): void
    {
        if ($this->columnType('price_codes', 'selling_price') !== 'decimal(10,5)') {
            Schema::table('price_codes', function (Blueprint $table) {
                $table->decimal('selling_price', 10, 5)->change();
            });
        }

        foreach (['uom_total', 'uom_total_less_disc'] as $column) {
            if ($this->columnType('sales_order_product_uoms', $column) !== 'decimal(15,2)') {
                Schema::table('sales_order_product_uoms', function (Blueprint $table) use ($column) {
                    $table->decimal($column, 15, 2)->change();
                });
            }
        }

        $this->makeNullable('brands', 'created_at', fn (Blueprint $table) => $table->timestamp('created_at')->nullable()->change());
        $this->makeNullable('ppuform_items', 'rtv_date', fn (Blueprint $table) => $table->date('rtv_date')->nullable()->change());
        $this->makeNullable('ppuforms', 'date_submitted', fn (Blueprint $table) => $table->date('date_submitted')->nullable()->change());
        $this->makeNullable('ppuforms', 'pickup_date', fn (Blueprint $table) => $table->date('pickup_date')->nullable()->change());
        $this->makeNullable('ppuforms', 'total_quantity', fn (Blueprint $table) => $table->integer('total_quantity')->default(0)->nullable()->change());
        $this->makeNullable('ppuforms', 'total_amount', fn (Blueprint $table) => $table->decimal('total_amount', 15, 2)->default(0)->nullable()->change());
        $this->makeNullable('products', 'brand', fn (Blueprint $table) => $table->string('brand')->nullable()->change());
        $this->makeNullable('products', 'brand_id', fn (Blueprint $table) => $table->unsignedBigInteger('brand_id')->nullable()->change());
        $this->makeNullable('sales_orders', 'paf_number', fn (Blueprint $table) => $table->string('paf_number')->nullable()->change());
        $this->makeNullable('settings', 'mcp_deadline', fn (Blueprint $table) => $table->integer('mcp_deadline')->nullable()->change());
        $this->makeNullable('shipping_addresses', 'ship_to_name', fn (Blueprint $table) => $table->string('ship_to_name')->nullable()->change());
        $this->makeNullable('users', 'notify_email', fn (Blueprint $table) => $table->string('notify_email')->nullable()->change());
    }

    /**
     * Live allows one notify email on several users and indexes the columns its lookups use.
     */
    private function alignIndexesWithLive(): void
    {
        if ($this->indexNamed('users', 'users_notify_email_unique')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_notify_email_unique');
            });
        }

        if (!$this->hasIndexStartingWith('users', ['hub_user_id'])) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('hub_user_id');
            });
        }

        if (!$this->hasIndexStartingWith('products', ['brand_id'])) {
            Schema::table('products', function (Blueprint $table) {
                $table->index('brand_id');
            });
        }

        foreach (['branch_login_id', 'user_branch_schedule_id'] as $column) {
            if (!$this->hasIndexStartingWith('weekly_activity_report_branches', [$column])) {
                Schema::table('weekly_activity_report_branches', function (Blueprint $table) use ($column) {
                    $table->index($column);
                });
            }
        }

        $duplicateAccountCodes = DB::table('accounts')
            ->select('company_id', 'account_code')
            ->groupBy('company_id', 'account_code')
            ->havingRaw('count(*) > 1')
            ->exists();

        if (!$this->hasIndexStartingWith('accounts', ['company_id', 'account_code']) && !$duplicateAccountCodes) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->unique(['company_id', 'account_code'], 'uq_accounts_company_code');
            });
        }
    }

    private function makeNullable(string $table, string $column, Closure $change): void
    {
        $definition = collect(Schema::getColumns($table))->firstWhere('name', $column);

        if ($definition !== null && !$definition['nullable']) {
            Schema::table($table, $change);
        }
    }

    private function columnType(string $table, string $column): ?string
    {
        return collect(Schema::getColumns($table))->firstWhere('name', $column)['type'] ?? null;
    }

    private function indexNamed(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn (array $index) => $index['name'] === $name);
    }

    /**
     * @param array<int, string> $columns
     */
    private function hasIndexStartingWith(string $table, array $columns): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index) => array_slice($index['columns'], 0, count($columns)) === $columns);
    }
};
