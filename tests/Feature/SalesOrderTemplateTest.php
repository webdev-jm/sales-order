<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

use App\Http\Livewire\SalesOrderTemplate\Index;
use App\Models\Account;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Models\User;

use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;

class SalesOrderTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RoleSeeder::class);
        $this->seed(SettingSeeder::class);
        Storage::fake('local');
    }

    private function actingAsSuperadmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('superadmin');
        $this->actingAs($user);
        return $user;
    }

    // -------------------------------------------------------------------------
    // Route & page access
    // -------------------------------------------------------------------------

    public function test_unauthenticated_user_is_redirected(): void
    {
        $this->get(route('sales-order-template.index'))->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_page(): void
    {
        $this->actingAsSuperadmin();
        $this->get(route('sales-order-template.index'))->assertOk();
    }

    // -------------------------------------------------------------------------
    // Component renders upload form on step 1
    // -------------------------------------------------------------------------

    public function test_component_renders_upload_form_with_accounts_dropdown(): void
    {
        $this->actingAsSuperadmin();
        Account::factory()->create(['account_name' => 'PUREGOLD PRICE CLUB', 'account_code' => 'PG001']);

        Livewire::test(Index::class)
            ->assertSet('step', 1)
            ->assertSee('PUREGOLD PRICE CLUB')
            ->assertSee('PG001');
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_upload_requires_account_ids(): void
    {
        $this->actingAsSuperadmin();

        Livewire::test(Index::class)
            ->set('account_ids', [])
            ->call('upload')
            ->assertHasErrors(['account_ids' => 'required']);
    }

    public function test_upload_requires_csv_file(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', null)
            ->call('upload')
            ->assertHasErrors(['file' => 'required']);
    }

    public function test_upload_rejects_non_csv_file(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', UploadedFile::fake()->create('data.xlsx', 100, 'application/vnd.ms-excel'))
            ->call('upload')
            ->assertHasErrors(['file']);
    }

    // -------------------------------------------------------------------------
    // Parsing — Luzon row format
    // -------------------------------------------------------------------------

    public function test_luzon_row_parses_all_22_columns(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();

        $product = Product::factory()->create(['stock_code' => '344150']);
        ShippingAddress::factory()->create([
            'account_id'   => $account->id,
            'address_code' => '01033',
            'ship_to_name' => 'PPCI-SUBIC NORTH',
            'building'     => 'Bldg A',
            'street'       => 'Main St',
            'city'         => 'Manila',
        ]);

        $csv = $this->buildLuzonCsvRow();
        $file = UploadedFile::fake()->createWithContent('orders.csv', $csv);

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload')
            ->assertSet('step', 2);

        $rows = Session::get('so_template_rows');
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertEquals('2026-05-29', $row['po_date']);
        $this->assertEquals('0065840556-00', $row['po_number']);
        $this->assertEquals('PPCI-SUBIC (FERTUNA)', $row['store_name']);
        $this->assertEquals('1033', $row['store_code']);
        $this->assertEquals('2026-06-15', $row['delivery_date']);
        $this->assertEquals('2026-06-17', $row['cancellation_date']);
        $this->assertEquals('GRAND CHAMONIX MARKETING INC.', $row['depot']);
        $this->assertEquals('PPCI-SUBIC NORTH', $row['del_loc']);
        $this->assertEquals('OUTRIGHT', $row['po_remarks']);
        $this->assertEquals('344150-8', $row['raw_sku']);
        $this->assertEquals('344150', $row['sku_code']);
        $this->assertEquals('KOJIESAN DREAMWHITE 65G PROMO', $row['description']);
        $this->assertEquals('1.00', $row['qty']);
        $this->assertEquals('3520.8000', $row['list_price']);
        $this->assertEquals('3246.1776', $row['amount']);
        $this->assertEquals($product->id, $row['product_id']);
        $this->assertEquals('ok', $row['lookup_status']);
    }

    // -------------------------------------------------------------------------
    // Parsing — SIMPLE/Vismin row format
    // -------------------------------------------------------------------------

    public function test_simple_row_parses_all_22_columns(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();

        $product = Product::factory()->create(['stock_code' => '298436']);
        ShippingAddress::factory()->create([
            'account_id'   => $account->id,
            'address_code' => '778',
            'ship_to_name' => 'PG-SAN JOSE DE BUENAVISTA',
            'building'     => '',
            'street'       => 'National Hwy',
            'city'         => 'Antique',
        ]);

        $csv = $this->buildSimpleCsvRow();
        $file = UploadedFile::fake()->createWithContent('orders.csv', $csv);

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload')
            ->assertSet('step', 2);

        $rows = Session::get('so_template_rows');
        $this->assertCount(1, $rows);

        $row = $rows[0];
        $this->assertEquals('2026-05-28', $row['po_date']);
        $this->assertEquals('65812536-00', $row['po_number']);
        $this->assertEquals('778', $row['store_code']);
        $this->assertEquals('298436-7', $row['raw_sku']);
        $this->assertEquals('298436', $row['sku_code']);
        $this->assertEquals('KOJIESAN SOAP SLIGHT135G PROMO', $row['description']);
        $this->assertEquals($product->id, $row['product_id']);
    }

    // -------------------------------------------------------------------------
    // Lookup status flags
    // -------------------------------------------------------------------------

    public function test_sku_not_found_status_when_product_missing(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();
        ShippingAddress::factory()->create([
            'account_id'   => $account->id,
            'address_code' => '01033',
            'ship_to_name' => 'PPCI-SUBIC NORTH',
            'building'     => '',
            'street'       => '',
            'city'         => '',
        ]);

        $file = UploadedFile::fake()->createWithContent('orders.csv', $this->buildLuzonCsvRow());

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload');

        $row = Session::get('so_template_rows')[0];
        $this->assertEquals('sku_not_found', $row['lookup_status']);
        $this->assertNull($row['product_id']);
    }

    public function test_address_not_found_status_when_shipping_address_missing(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();
        Product::factory()->create(['stock_code' => '344150']);

        $file = UploadedFile::fake()->createWithContent('orders.csv', $this->buildLuzonCsvRow());

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload');

        $row = Session::get('so_template_rows')[0];
        $this->assertEquals('address_not_found', $row['lookup_status']);
        $this->assertNull($row['shipping_address_id']);
    }

    public function test_both_not_found_status_when_neither_matches(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();

        $file = UploadedFile::fake()->createWithContent('orders.csv', $this->buildLuzonCsvRow());

        Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload');

        $row = Session::get('so_template_rows')[0];
        $this->assertEquals('both_not_found', $row['lookup_status']);
    }

    // -------------------------------------------------------------------------
    // Summary counts
    // -------------------------------------------------------------------------

    public function test_summary_counts_are_correct(): void
    {
        $this->actingAsSuperadmin();
        $account = Account::factory()->create();
        Product::factory()->create(['stock_code' => '344150']);
        ShippingAddress::factory()->create([
            'account_id'   => $account->id,
            'address_code' => '01033',
            'ship_to_name' => 'PPCI-SUBIC NORTH',
            'building'     => '',
            'street'       => '',
            'city'         => '',
        ]);

        // Two identical rows: one will be ok, one will also be ok
        $csv = $this->buildLuzonCsvRow() . "\n" . $this->buildLuzonCsvRow();
        $file = UploadedFile::fake()->createWithContent('orders.csv', $csv);

        $component = Livewire::test(Index::class)
            ->set('account_ids', [$account->id])
            ->set('file', $file)
            ->call('upload');

        $summary = $component->get('summary');
        $this->assertEquals(2, $summary['total']);
        $this->assertEquals(2, $summary['ok']);
        $this->assertEquals(0, $summary['sku_not_found']);
    }

    // -------------------------------------------------------------------------
    // Reset
    // -------------------------------------------------------------------------

    public function test_reset_clears_session_and_returns_to_step_1(): void
    {
        $this->actingAsSuperadmin();
        Session::put('so_template_rows', [['foo' => 'bar']]);

        Livewire::test(Index::class)
            ->set('step', 2)
            ->call('resetUpload')
            ->assertSet('step', 1);

        $this->assertNull(Session::get('so_template_rows'));
    }

    // -------------------------------------------------------------------------
    // CSV fixture helpers
    // -------------------------------------------------------------------------

    private function buildLuzonCsvRow(): string
    {
        // 66 positional columns, col 0 is empty (Luzon format)
        $cols = array_fill(0, 66, '');
        $cols[1]  = '2026-05-29T00:00:00';
        $cols[4]  = '0065840556-00';
        $cols[6]  = 'PPCI-SUBIC (FERTUNA)';
        $cols[30] = ':name:GRAND CHAMONIX MARKETING INC.:address:Megaland Industrial Park:contact:';
        $cols[34] = ':dlvLocation:PPCI-SUBIC NORTH:dlvAddress:MANILA MLA PHILIPPINES';
        $cols[36] = '01033';
        $cols[41] = '2026-06-15';
        $cols[47] = '2026-06-17T00:00:00';
        $cols[51] = ':status:3:type:A:remarks::currency:PHP -PHILIPPINE PESO:discounts::skuAllow::netTotal:6711.4224:skuDs:567.7776:otherDs:.00:notes:OUTRIGHT';
        $cols[55] = '344150-8';
        $cols[56] = 'KOJIESAN DREAMWHITE 65G PROMO';
        $cols[58] = '1.00';
        $cols[60] = '3520.8000';
        $cols[64] = '3246.1776';

        return implode(',', $cols);
    }

    private function buildSimpleCsvRow(): string
    {
        // SIMPLE format: col 0 has a date
        $cols = array_fill(0, 29, '');
        $cols[0]  = '2026-05-28';
        $cols[1]  = '65812536-00';
        $cols[10] = '2026-06-08';
        $cols[14] = '2026-06-10';
        $cols[16] = ':vendor:014531 BEVI BEAUTY ELEMENTS VENTURES INC. :tel:09176382270 :buyer:301 CONNIE AVILA :terms:030 30 DAYS :status:3:type:A:dlvLocation:00778 PG-SAN JOSE DE BUENAVISTA:dlvAddress:BRGY 2 SAN JOSE ANTIQUE:notes:';
        $cols[19] = ':description:KOJIESAN SOAP SLIGHT135G PROMO:sku:000298436-7:buyUM:C24:buyCost:2656.8000:vendorSpecDs:-7.8%:currency:PHILIPPINE PESO';
        $cols[20] = '1';
        $cols[25] = '2449.5696';

        return implode(',', $cols);
    }
}
