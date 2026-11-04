<?php

namespace Tests\Unit;

use App\Services\ScanConfidence;
use Tests\TestCase;

class ScanConfidenceTest extends TestCase
{
    /**
     * A scanned PO in the shape SalesOrderPdfReader returns, read cleanly unless a test changes it.
     *
     * @param array<string, mixed> $changes
     * @param array<int, array<string, mixed>>|null $lines
     * @return array<string, mixed>
     */
    private function order(array $changes = [], ?array $lines = null): array
    {
        $order = [
            'po_number'           => 'PO-CONF-' . uniqid(),
            'ship_date'           => now()->addDays(5)->toDateString(),
            'ship_date_error'     => null,
            'shipping_address_id' => 7,
            'address_code'        => 'ST-01',
            'header'              => [
                'po_date'               => now()->toDateString(),
                'po_date_printed'       => now()->toDateString(),
                'delivery_date_printed' => now()->addDays(5)->toDateString(),
                'store_code'            => 'ST-01',
                'store_name'            => 'SAMPLE MART',
                'printed_total'         => '1200',
                'confidence'            => ['po_number' => 98, 'po_date' => 97, 'delivery_date' => 96, 'store' => 95, 'total' => 95],
                'unclear'               => '',
                'page'                  => 1,
            ],
            'lines'               => $lines ?? [$this->line()],
        ];

        return array_replace_recursive($order, $changes);
    }

    /**
     * @param array<string, mixed> $changes
     * @return array<string, mixed>
     */
    private function line(array $changes = []): array
    {
        return $changes + [
            'sku' => '100234', 'description' => 'CHOCO WAFER', 'quantity' => 12.0, 'uom' => 'CS', 'so_uom' => 'CS',
            'unit_price' => '100', 'amount' => '1200', 'product_id' => 5, 'stock_code' => 'KS-01', 'product' => 'Choco Wafer',
            'page' => 1, 'ai_confidence' => 96, 'ai_unclear' => '',
        ];
    }

    public function test_a_clean_read_is_high(): void
    {
        $result = ScanConfidence::assess($this->order(), null);

        $this->assertSame('high', $result['level']);
        $this->assertSame(0, $result['low']);
        $this->assertSame([], $result['lines'][0]['reasons']);
    }

    public function test_a_line_without_a_product_is_low(): void
    {
        $result = ScanConfidence::assess($this->order([], [$this->line(['product_id' => null, 'stock_code' => null])]), null);

        $this->assertSame('low', $result['lines'][0]['level']);
        $this->assertStringContainsString('No product for item code 100234', implode(' ', $result['lines'][0]['reasons']));
        $this->assertSame(1, $result['low']);
    }

    public function test_an_amount_that_does_not_match_the_quantity_is_low_and_suggests_the_quantity_that_fits(): void
    {
        $result = ScanConfidence::assess($this->order(['header' => ['printed_total' => '1300']], [$this->line(['quantity' => 12.0, 'amount' => '1300'])]), null);

        $reasons = implode(' ', $result['lines'][0]['reasons']);
        $this->assertSame('low', $result['lines'][0]['level']);
        $this->assertStringContainsString('12 × 100.00 is 1,200.00, but the PO shows 1,300.00.', $reasons);
        $this->assertStringContainsString('A quantity of 13 would match.', $reasons);
    }

    public function test_the_ai_rating_and_its_note_are_used(): void
    {
        $result = ScanConfidence::assess($this->order([], [$this->line(['ai_confidence' => 62, 'ai_unclear' => 'quantity is smudged'])]), null);

        $this->assertSame(62, $result['lines'][0]['score']);
        $this->assertSame('The AI noted: quantity is smudged.', $result['lines'][0]['reasons'][0]);
    }

    public function test_a_line_without_a_printed_unit_is_medium(): void
    {
        $result = ScanConfidence::assess($this->order([], [$this->line(['uom' => ''])]), null);

        $this->assertSame('medium', $result['lines'][0]['level']);
        $this->assertStringContainsString('No unit is printed, so CS was used.', implode(' ', $result['lines'][0]['reasons']));
    }

    public function test_a_store_that_is_not_a_shipping_address_is_low(): void
    {
        $result = ScanConfidence::assess($this->order(['shipping_address_id' => null]), null);

        $this->assertSame('low', $result['fields']['ship_to']['level']);
        $this->assertSame('low', $result['level']);
    }

    public function test_ship_date_problems_lower_the_ship_date(): void
    {
        $unreadable = ScanConfidence::assess($this->order(['ship_date' => null, 'ship_date_error' => 'Delivery date could not be read.']), null);
        $this->assertSame('low', $unreadable['fields']['ship_date']['level']);

        $beforePo = ScanConfidence::assess($this->order(['ship_date' => now()->subDay()->toDateString()]), null);
        $this->assertSame('low', $beforePo['fields']['ship_date']['level']);
        $this->assertStringContainsString('before the PO date', implode(' ', $beforePo['fields']['ship_date']['reasons']));

        $past = ScanConfidence::assess($this->order(['ship_date' => now()->subDays(3)->toDateString(), 'header' => ['po_date' => now()->subDays(10)->toDateString()]]), null);
        $this->assertSame('medium', $past['fields']['ship_date']['level']);
    }

    public function test_a_total_that_the_lines_do_not_add_up_to_is_medium(): void
    {
        $result = ScanConfidence::assess($this->order(['header' => ['printed_total' => '1500']]), null);

        $this->assertSame('medium', $result['fields']['total']['level']);
        $this->assertStringContainsString('The lines add up to 1,200.00, not the printed 1,500.00.', implode(' ', $result['fields']['total']['reasons']));
    }

    public function test_a_total_mismatch_names_the_lines_without_a_quantity(): void
    {
        $order = $this->order([], [$this->line(), $this->line(['quantity' => 0.0, 'amount' => '', 'ai_unclear' => 'The quantity could not be read'])]);
        $order['header']['printed_total'] = '2400';

        $result = ScanConfidence::assess($order, null);

        $this->assertStringContainsString('1 line has no quantity.', implode(' ', $result['fields']['total']['reasons']));
        $this->assertSame('low', $result['lines'][1]['level']);
    }

    public function test_a_line_without_an_item_code_says_so(): void
    {
        $result = ScanConfidence::assess($this->order([], [$this->line(['sku' => '', 'product_id' => null, 'stock_code' => null])]), null);

        $this->assertContains('No item code was read for this line, so it cannot be ordered.', $result['lines'][0]['reasons']);
    }

    public function test_a_line_the_order_would_leave_out_is_low(): void
    {
        $result = ScanConfidence::assess($this->order([], [$this->line(['sms_prices' => [], 'sms_problem' => 'No price code \'A\' is set up for this product. The order would leave this line out.'])]), null);

        $this->assertSame('low', $result['lines'][0]['level']);
        $this->assertContains('No price code \'A\' is set up for this product. The order would leave this line out.', $result['lines'][0]['reasons']);
    }

    public function test_a_po_price_that_fits_another_unit_is_low_and_names_that_unit(): void
    {
        $line = $this->line(['unit_price' => '100', 'amount' => '1200', 'sms_prices' => ['CS' => 1200.0, 'PCS' => 100.0], 'sms_problem' => null]);

        $result = ScanConfidence::assess($this->order([], [$line]), null);

        $this->assertSame('low', $result['lines'][0]['level']);
        $this->assertStringContainsString('fits PCS', implode(' ', $result['lines'][0]['reasons']));
    }

    public function test_a_po_price_far_from_the_sms_price_is_low(): void
    {
        $line = $this->line(['unit_price' => '450', 'amount' => '5400', 'sms_prices' => ['CS' => 100.0], 'sms_problem' => null]);

        $result = ScanConfidence::assess($this->order(['header' => ['printed_total' => '5400']], [$line]), null);

        $this->assertSame('low', $result['lines'][0]['level']);
        $this->assertStringContainsString('far from the SMS price of 100.00 per CS', implode(' ', $result['lines'][0]['reasons']));
    }

    public function test_a_po_price_with_vat_or_a_discount_still_counts_as_the_same_price(): void
    {
        $withVat = $this->line(['unit_price' => '112', 'amount' => '1344', 'sms_prices' => ['CS' => 100.0, 'PCS' => 9.0], 'sms_problem' => null]);
        $discounted = $this->line(['unit_price' => '70', 'amount' => '840', 'sms_prices' => ['CS' => 100.0], 'sms_problem' => null]);

        $result = ScanConfidence::assess($this->order(['header' => ['printed_total' => '2184']], [$withVat, $discounted]), null);

        $this->assertSame('high', $result['lines'][0]['level']);
        $this->assertSame('high', $result['lines'][1]['level']);
    }

    public function test_without_a_printed_unit_price_the_amount_is_compared(): void
    {
        $line = $this->line(['unit_price' => '', 'amount' => '120', 'sms_prices' => ['CS' => 120.0, 'PCS' => 10.0], 'sms_problem' => null]);

        $result = ScanConfidence::assess($this->order(['header' => ['printed_total' => '120']], [$line]), null);

        $this->assertStringContainsString('fits PCS', implode(' ', $result['lines'][0]['reasons']));
    }

    public function test_lines_left_out_are_not_rated(): void
    {
        $lines = [$this->line(), $this->line(['sku' => '999', 'product_id' => null, 'stock_code' => null])];

        $result = ScanConfidence::assess($this->order([], $lines), null, [0 => true, 1 => false]);

        $this->assertArrayNotHasKey(1, $result['lines']);
        $this->assertSame(0, $result['low']);
    }

    public function test_an_unrated_scan_is_medium_rather_than_high(): void
    {
        $order = $this->order(['header' => ['confidence' => []]], [$this->line(['ai_confidence' => null])]);

        $this->assertSame('medium', ScanConfidence::assess($order, null)['level']);
    }

    public function test_dates_are_searched_as_they_are_commonly_printed(): void
    {
        $result = ScanConfidence::assess($this->order(['header' => ['po_date' => '2026-10-14']]), null);

        $this->assertContains('10/14/2026', $result['fields']['po_date']['find']);
        $this->assertContains('14/10/2026', $result['fields']['po_date']['find']);
    }

    public function test_levels_follow_the_thresholds(): void
    {
        $this->assertSame('high', ScanConfidence::level(90));
        $this->assertSame('medium', ScanConfidence::level(89));
        $this->assertSame('medium', ScanConfidence::level(70));
        $this->assertSame('low', ScanConfidence::level(69));
    }
}
