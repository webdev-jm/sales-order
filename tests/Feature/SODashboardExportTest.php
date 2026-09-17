<?php

namespace Tests\Feature;

use App\Exports\SODashboardExport;
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
 * The dashboard export walked $account_login->account->company straight through,
 * so a sales order whose account (or company, or login) had been soft deleted
 * blew up with "Attempt to read property 'company' on null". The trashed rows
 * still have to appear in the export, and an order without products must not
 * crash on the summary row either.
 */
class SODashboardExportTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('activitylog.enabled', false);
    }

    private function salesOrderFor(AccountLogin $account_login): SalesOrder
    {
        return SalesOrder::factory()->create([
            'account_login_id' => $account_login->id,
            'order_date'       => date('Y-m-d'),
            'status'           => 'finalized',
            'upload_status'    => 1,
        ]);
    }

    private function accountLogin(array $account_attributes = []): AccountLogin
    {
        $account = Account::factory()->create(array_merge([
            'company_id'  => Company::factory()->create()->id,
            'discount_id' => null,
        ], $account_attributes));

        return AccountLogin::factory()->create([
            'user_id'    => User::factory()->create(['status' => 'active'])->id,
            'account_id' => $account->id,
        ]);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function exportRows(): array
    {
        return (new SODashboardExport(null, null, null))->collection()[3];
    }

    public function test_soft_deleted_account_still_exports_its_company_and_codes(): void
    {
        $account_login = $this->accountLogin();
        $account       = $account_login->account;
        $sales_order   = $this->salesOrderFor($account_login);

        $account->delete();

        $rows = collect($this->exportRows())
            ->where(7, $sales_order->control_number)
            ->values();

        $this->assertCount(1, $rows);
        $this->assertSame($account->company->name, $rows[0][3]);
        $this->assertSame($account->account_code, $rows[0][4]);
        $this->assertSame($account->account_name, $rows[0][6]);
    }

    public function test_soft_deleted_company_still_exports_its_name(): void
    {
        $account_login = $this->accountLogin();
        $company       = $account_login->account->company;
        $sales_order   = $this->salesOrderFor($account_login);

        $company->delete();

        $rows = collect($this->exportRows())
            ->where(7, $sales_order->control_number)
            ->values();

        $this->assertCount(1, $rows);
        $this->assertSame($company->name, $rows[0][3]);
    }

    public function test_account_login_without_an_account_exports_blank_account_columns(): void
    {
        $account_login = $this->accountLogin();
        $account_login->update(['account_id' => null]);
        $sales_order = $this->salesOrderFor($account_login->refresh());

        $rows = collect($this->exportRows())
            ->where(7, $sales_order->control_number)
            ->values();

        $this->assertCount(1, $rows);
        $this->assertSame(['', '', '', ''], [$rows[0][3], $rows[0][4], $rows[0][5], $rows[0][6]]);
        $this->assertSame($account_login->user->email, $rows[0][1]);
    }

    public function test_sales_order_without_products_exports_a_blank_part(): void
    {
        $sales_order = $this->salesOrderFor($this->accountLogin());

        $rows = collect($this->exportRows())
            ->where(7, $sales_order->control_number)
            ->values();

        $this->assertCount(1, $rows);
        $this->assertSame('', $rows[0][15]);
        $this->assertEquals($sales_order->grand_total, $rows[0][22]);
    }

    public function test_product_rows_carry_the_account_columns(): void
    {
        $account_login = $this->accountLogin();
        $account       = $account_login->account;
        $sales_order   = $this->salesOrderFor($account_login);

        $sales_order_product = SalesOrderProduct::factory()->create([
            'sales_order_id' => $sales_order->id,
            'product_id'     => Product::factory()->create([
                'brand_id' => Brand::factory()->create(['brand' => 'KOJIE SAN'])->id,
            ])->id,
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

        $account->delete();

        $rows = collect($this->exportRows())
            ->where(7, $sales_order->control_number)
            ->values();

        $this->assertCount(2, $rows);
        $this->assertSame($account->company->name, $rows[0][3]);
        $this->assertSame('CS', $rows[0][19]);
        $this->assertSame(1, $rows[0][15]);
    }
}
