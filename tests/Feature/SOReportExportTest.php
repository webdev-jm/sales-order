<?php

namespace Tests\Feature;

use App\Exports\SOReportExport;
use App\Models\Account;
use App\Models\AccountLogin;
use App\Models\Brand;
use App\Models\Company;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderProduct;
use App\Models\SalesOrderProductUom;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Same defect as the SO dashboard export: the monthly report walked
 * $account_login->account->company (and $order_product->product) straight
 * through, so any soft-deleted account, company or product threw
 * "Attempt to read property ... on null" instead of exporting the row.
 */
class SOReportExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('activitylog.enabled', false);
    }

    private function accountLogin(): AccountLogin
    {
        $account = Account::factory()->create([
            'company_id'  => Company::factory()->create()->id,
            'discount_id' => null,
        ]);

        return AccountLogin::factory()->create([
            'user_id'    => User::factory()->create(['status' => 'active'])->id,
            'account_id' => $account->id,
        ]);
    }

    private function salesOrderWithProduct(AccountLogin $account_login, ?Product $product = null): SalesOrder
    {
        $sales_order = SalesOrder::factory()->create([
            'account_login_id' => $account_login->id,
            'order_date'       => date('Y-m-d'),
            'status'           => 'finalized',
            'upload_status'    => 1,
        ]);

        $sales_order_product = SalesOrderProduct::factory()->create([
            'sales_order_id' => $sales_order->id,
            'product_id'     => ($product ?: $this->product())->id,
            'part'           => 1,
        ]);

        SalesOrderProductUom::factory()->create([
            'sales_order_product_id' => $sales_order_product->id,
            'uom'                    => 'CS',
            'quantity'               => 5,
            'uom_total'              => 100,
            'uom_total_less_disc'    => 100,
            'warehouse'              => 'WH02',
        ]);

        return $sales_order;
    }

    private function product(): Product
    {
        return Product::factory()->create([
            'brand_id' => Brand::factory()->create(['brand' => 'KOJIE SAN'])->id,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<int, mixed>>
     */
    private function exportRowsFor(SalesOrder $sales_order)
    {
        $rows = (new SOReportExport(date('Y'), date('m'), null))->collection()[3];

        return collect($rows)->where(7, $sales_order->control_number)->values();
    }

    public function test_soft_deleted_account_still_exports_its_company_and_codes(): void
    {
        $account_login = $this->accountLogin();
        $account       = $account_login->account;
        $sales_order   = $this->salesOrderWithProduct($account_login);

        $account->delete();

        $rows = $this->exportRowsFor($sales_order);

        $this->assertCount(1, $rows);
        $this->assertSame($account->company->name, $rows[0][3]);
        $this->assertSame($account->account_code, $rows[0][4]);
        $this->assertSame($account->account_name, $rows[0][6]);
    }

    public function test_soft_deleted_company_still_exports_its_name(): void
    {
        $account_login = $this->accountLogin();
        $company       = $account_login->account->company;
        $sales_order   = $this->salesOrderWithProduct($account_login);

        $company->delete();

        $rows = $this->exportRowsFor($sales_order);

        $this->assertCount(1, $rows);
        $this->assertSame($company->name, $rows[0][3]);
    }

    public function test_account_login_without_an_account_exports_blank_account_columns(): void
    {
        $account_login = $this->accountLogin();
        $account_login->update(['account_id' => null]);
        $sales_order = $this->salesOrderWithProduct($account_login->refresh());

        $rows = $this->exportRowsFor($sales_order);

        $this->assertCount(1, $rows);
        $this->assertSame(['', '', '', ''], [$rows[0][3], $rows[0][4], $rows[0][5], $rows[0][6]]);
        $this->assertSame($account_login->user->email, $rows[0][1]);
    }

    public function test_soft_deleted_product_still_exports_its_stock_code(): void
    {
        $product     = $this->product();
        $sales_order = $this->salesOrderWithProduct($this->accountLogin(), $product);

        $product->delete();

        $rows = $this->exportRowsFor($sales_order);

        $this->assertCount(1, $rows);
        $this->assertSame($product->stock_code, $rows[0][16]);
        $this->assertSame('CS', $rows[0][19]);
    }
}
