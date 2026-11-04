<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountProductReference;
use App\Models\AccountShipAddressMapping;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ShippingAddress;
use App\Services\SoTemplateLookup;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SoTemplateLookupTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private SoTemplateLookup $lookup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->account = Account::factory()->create();
        $this->lookup = new SoTemplateLookup;
    }

    private function product(string $stockCode): Product
    {
        return Product::factory()->create(['brand_id' => Brand::factory()->create()->id, 'stock_code' => $stockCode . '-' . uniqid()]);
    }

    private function reference(string $accountReference, Product $product): void
    {
        AccountProductReference::create([
            'account_id'        => $this->account->id,
            'product_id'        => $product->id,
            'account_reference' => $accountReference,
            'description'       => 'TEST',
            'active'            => 1,
        ]);
    }

    private function address(string $code): ShippingAddress
    {
        return ShippingAddress::factory()->create(['account_id' => $this->account->id, 'address_code' => $code . '-' . uniqid()]);
    }

    public function test_an_unknown_letter_sku_does_not_match_a_letter_reference(): void
    {
        $this->reference('KS003', $this->product('LOOKUP-A'));

        $found = $this->lookup->product('ABC-XYZ', 'ABC-XYZ', [$this->account->id]);

        $this->assertNull($found['product_id']);
    }

    public function test_a_letter_sku_still_matches_its_own_reference(): void
    {
        $product = $this->product('LOOKUP-B');
        $this->reference('KS003', $this->product('LOOKUP-C'));
        $this->reference('MV-78279', $product);

        $found = $this->lookup->product('MV-78279', 'MV-78279', [$this->account->id]);

        $this->assertSame($product->id, $found['product_id']);
    }

    public function test_a_number_sku_matches_a_reference_with_leading_zeros(): void
    {
        $product = $this->product('LOOKUP-D');
        $this->reference('KS003', $this->product('LOOKUP-E'));
        $this->reference('00987654', $product);

        $found = $this->lookup->product('987654', '987654', [$this->account->id]);

        $this->assertSame($product->id, $found['product_id']);
    }

    public function test_a_zero_sku_does_not_match_a_letter_reference(): void
    {
        $this->reference('KS003', $this->product('LOOKUP-F'));

        $this->assertNull($this->lookup->product('0', '0', [$this->account->id])['product_id']);
    }

    public function test_a_store_code_without_a_name_does_not_match_a_mapping_with_a_blank_reference(): void
    {
        AccountShipAddressMapping::create([
            'account_id'          => $this->account->id,
            'shipping_address_id' => $this->address('OTHER')->id,
            'reference1'          => 'S-111',
            'reference2'          => '',
        ]);

        $found = $this->lookup->shippingAddress('S-999', '', [$this->account->id]);

        $this->assertNull($found['shipping_address_id']);
    }

    public function test_a_store_name_without_a_code_does_not_match_a_mapping_with_a_blank_reference(): void
    {
        AccountShipAddressMapping::create([
            'account_id'          => $this->account->id,
            'shipping_address_id' => $this->address('OTHER')->id,
            'reference1'          => '',
            'reference2'          => 'SM MEGAMALL',
        ]);

        $found = $this->lookup->shippingAddress('', 'ROBINSONS GALLERIA', [$this->account->id]);

        $this->assertNull($found['shipping_address_id']);
    }

    public function test_a_mapping_is_still_found_by_code_or_by_name_ignoring_spaces_and_case(): void
    {
        $byCode = $this->address('BYCODE');
        $byName = $this->address('BYNAME');
        AccountShipAddressMapping::create(['account_id' => $this->account->id, 'shipping_address_id' => $byCode->id, 'reference1' => 'S-111', 'reference2' => null]);
        AccountShipAddressMapping::create(['account_id' => $this->account->id, 'shipping_address_id' => $byName->id, 'reference1' => 'S-222', 'reference2' => 'SM Megamall']);

        $this->assertSame($byCode->id, $this->lookup->shippingAddress('S-111', '', [$this->account->id])['shipping_address_id']);
        $this->assertSame($byName->id, $this->lookup->shippingAddress('', 'sm  megamall', [$this->account->id])['shipping_address_id']);
    }
}
