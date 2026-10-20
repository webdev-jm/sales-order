<?php

namespace App\Services\Ai;

use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use Illuminate\Database\Eloquent\Builder;

/**
 * Read-only sales order figures for the AI features, computed the way the SO Dashboard and the
 * Sales Order report compute them: an order counts once it is finalized and uploaded to SYSPRO.
 * The AI is only ever shown these numbers; it never queries the database itself.
 *
 * Filters: date_from and date_to (Y-m-d, order date) or a list of exact dates, account_ids (null for
 * every account), group_code (the salesman's group) and orders ("uploaded" by default, or "all").
 *
 * @phpstan-type Filters array{date_from?: string|null, date_to?: string|null, dates?: array<int, string>|null, account_ids?: array<int, int>|null, group_code?: string|null, orders?: string|null}
 */
class SalesFigures
{
    /** Most rows any list returns, so a reply stays small. */
    public const MAX_ROWS = 25;

    /**
     * @param Filters $filters
     * @return array{orders: int, quantity: float, gross_sales: float, net_sales: float}
     */
    public function summary(array $filters): array
    {
        $orders = $this->orders($filters);

        return [
            'orders'      => (clone $orders)->count(),
            'quantity'    => round((float) (clone $orders)->sum('total_quantity'), 2),
            'gross_sales' => round((float) (clone $orders)->sum('total_sales'), 2),
            'net_sales'   => round((float) (clone $orders)->sum('grand_total'), 2),
        ];
    }

    /**
     * Orders per status (draft, finalized, cancelled) and how many of them reached SYSPRO.
     *
     * @param Filters $filters
     * @return array<int, array{status: string, orders: int, uploaded: int}>
     */
    public function byStatus(array $filters): array
    {
        return $this->orders(['orders' => 'all'] + $filters)
            ->selectRaw('status, COUNT(*) as orders, SUM(CASE WHEN upload_status = 1 THEN 1 ELSE 0 END) as uploaded')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($row): array => ['status' => (string) $row->status, 'orders' => (int) $row->orders, 'uploaded' => (int) $row->uploaded])
            ->all();
    }

    /**
     * @param Filters $filters
     * @return array<int, array{account_code: string, account_name: string, orders: int, net_sales: float}>
     */
    public function byAccount(array $filters, int $limit = 10): array
    {
        return $this->orders($filters)
            ->join('account_logins', 'account_logins.id', '=', 'sales_orders.account_login_id')
            ->join('accounts', 'accounts.id', '=', 'account_logins.account_id')
            ->selectRaw('accounts.account_code, accounts.short_name, COUNT(sales_orders.id) as orders, SUM(sales_orders.grand_total) as net_sales')
            ->groupBy('accounts.account_code', 'accounts.short_name')
            ->orderByDesc('net_sales')
            ->limit($this->limit($limit))
            ->get()
            ->map(fn ($row): array => [
                'account_code' => (string) $row->account_code,
                'account_name' => (string) $row->short_name,
                'orders'       => (int) $row->orders,
                'net_sales'    => round((float) $row->net_sales, 2),
            ])
            ->all();
    }

    /**
     * Best-selling products by quantity or by gross sales.
     *
     * @param Filters $filters
     * @return array<int, array{stock_code: string, description: string, brand: string, quantity: float, gross_sales: float}>
     */
    public function topProducts(array $filters, int $limit = 10, string $rankBy = 'quantity'): array
    {
        $orderIds = $this->orders($filters)->select('sales_orders.id');

        return SalesOrderProduct::query()
            ->join('products', 'products.id', '=', 'sales_order_products.product_id')
            ->whereIn('sales_order_products.sales_order_id', $orderIds)
            ->selectRaw('products.stock_code, products.description, products.brand, SUM(sales_order_products.total_quantity) as quantity, SUM(sales_order_products.total_sales) as gross_sales')
            ->groupBy('products.stock_code', 'products.description', 'products.brand')
            ->orderByDesc($rankBy === 'sales' ? 'gross_sales' : 'quantity')
            ->limit($this->limit($limit))
            ->get()
            ->map(fn ($row): array => [
                'stock_code'  => (string) $row->stock_code,
                'description' => (string) $row->description,
                'brand'       => (string) $row->brand,
                'quantity'    => round((float) $row->quantity, 2),
                'gross_sales' => round((float) $row->gross_sales, 2),
            ])
            ->all();
    }

    /**
     * @param Filters $filters
     * @return array<int, array{brand: string, quantity: float, gross_sales: float}>
     */
    public function byBrand(array $filters, int $limit = 10): array
    {
        $orderIds = $this->orders($filters)->select('sales_orders.id');

        return SalesOrderProduct::query()
            ->join('products', 'products.id', '=', 'sales_order_products.product_id')
            ->whereIn('sales_order_products.sales_order_id', $orderIds)
            ->selectRaw("COALESCE(NULLIF(products.brand, ''), 'NO BRAND') as brand, SUM(sales_order_products.total_quantity) as quantity, SUM(sales_order_products.total_sales) as gross_sales")
            ->groupBy('brand')
            ->orderByDesc('gross_sales')
            ->limit($this->limit($limit))
            ->get()
            ->map(fn ($row): array => ['brand' => (string) $row->brand, 'quantity' => round((float) $row->quantity, 2), 'gross_sales' => round((float) $row->gross_sales, 2)])
            ->all();
    }

    /**
     * Matching orders, newest first.
     *
     * @param Filters&array{po_number?: string|null, control_number?: string|null, status?: string|null} $filters
     * @return array<int, array<string, mixed>>
     */
    public function findOrders(array $filters, int $limit = 10): array
    {
        return $this->orders(['orders' => 'all'] + $filters)
            ->with('account_login.account:id,account_code,short_name')
            ->when(filled($filters['po_number'] ?? null), fn (Builder $query) => $query->where('po_number', 'like', '%' . $filters['po_number'] . '%'))
            ->when(filled($filters['control_number'] ?? null), fn (Builder $query) => $query->where('control_number', 'like', '%' . $filters['control_number'] . '%'))
            ->when(filled($filters['status'] ?? null), fn (Builder $query) => $query->where('status', $filters['status']))
            ->orderByDesc('order_date')
            ->orderByDesc('sales_orders.id')
            ->limit($this->limit($limit))
            ->get()
            ->map(fn (SalesOrder $order): array => $this->describe($order))
            ->all();
    }

    /**
     * One order with its lines, or null when it does not exist within the filters.
     *
     * @param Filters $filters
     * @return array<string, mixed>|null
     */
    public function orderDetail(string $controlOrPoNumber, array $filters): ?array
    {
        $order = $this->orders(['orders' => 'all'] + $filters)
            ->with(['account_login.account:id,account_code,short_name', 'order_products.product:id,stock_code,description'])
            ->where(fn (Builder $query) => $query->where('control_number', $controlOrPoNumber)->orWhere('po_number', $controlOrPoNumber))
            ->orderByDesc('sales_orders.id')
            ->first();

        if ($order === null) {
            return null;
        }

        return $this->describe($order) + [
            'lines' => $order->order_products->take(100)->map(fn ($line): array => [
                'stock_code'  => (string) $line->product?->stock_code,
                'description' => (string) $line->product?->description,
                'quantity'    => round((float) $line->total_quantity, 2),
                'gross_sales' => round((float) $line->total_sales, 2),
            ])->values()->all(),
        ];
    }

    /**
     * The orders the filters cover.
     *
     * @param Filters $filters
     * @return Builder<SalesOrder>
     */
    private function orders(array $filters): Builder
    {
        $accountIds = $filters['account_ids'] ?? null;

        return SalesOrder::query()
            ->when(($filters['orders'] ?? 'uploaded') !== 'all', fn (Builder $query) => $query->where('sales_orders.status', 'finalized')->where('sales_orders.upload_status', 1))
            ->when(filled($filters['dates'] ?? null), fn (Builder $query) => $query->whereIn('sales_orders.order_date', $filters['dates']))
            ->when(filled($filters['date_from'] ?? null), fn (Builder $query) => $query->where('sales_orders.order_date', '>=', $filters['date_from']))
            ->when(filled($filters['date_to'] ?? null), fn (Builder $query) => $query->where('sales_orders.order_date', '<=', $filters['date_to']))
            ->when($accountIds !== null, fn (Builder $query) => $query->whereHas('account_login', fn (Builder $login) => $login->whereIn('account_id', $accountIds)))
            ->when(filled($filters['group_code'] ?? null), fn (Builder $query) => $query->whereHas('account_login.user', fn (Builder $user) => $user->where('group_code', $filters['group_code'])));
    }

    /**
     * @return array<string, mixed>
     */
    private function describe(SalesOrder $order): array
    {
        return [
            'control_number'    => (string) $order->control_number,
            'po_number'         => (string) $order->po_number,
            'account'           => trim(($order->account_login?->account?->account_code ?? '') . ' ' . ($order->account_login?->account?->short_name ?? '')),
            'order_date'        => (string) $order->order_date,
            'ship_date'         => (string) $order->ship_date,
            'status'            => (string) $order->status,
            'uploaded_to_syspro' => (int) $order->upload_status === 1,
            'syspro_reference'  => (string) $order->reference,
            'quantity'          => round((float) $order->total_quantity, 2),
            'net_sales'         => round((float) $order->grand_total, 2),
        ];
    }

    private function limit(int $limit): int
    {
        return max(1, min(self::MAX_ROWS, $limit));
    }
}
