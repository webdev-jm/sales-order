<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The order list, search, PO list and reports look sales orders up by these columns,
 * which had no index. Indexes that already exist (added by hand on a server) are skipped.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $indexes = [
        'sales_orders_control_number_index' => 'control_number',
        'sales_orders_po_number_index'      => 'po_number',
        'sales_orders_status_index'         => 'status',
        'sales_orders_order_date_index'     => 'order_date',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $missing = array_diff_key($this->indexes, array_flip($this->existingIndexes()));

        Schema::table('sales_orders', function (Blueprint $table) use ($missing) {
            foreach ($missing as $name => $column) {
                $table->index($column, $name);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $present = array_intersect_key($this->indexes, array_flip($this->existingIndexes()));

        Schema::table('sales_orders', function (Blueprint $table) use ($present) {
            foreach (array_keys($present) as $name) {
                $table->dropIndex($name);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    private function existingIndexes(): array
    {
        return collect(Schema::getConnection()->select('SHOW INDEX FROM sales_orders'))
            ->pluck('Key_name')
            ->unique()
            ->values()
            ->all();
    }
};
