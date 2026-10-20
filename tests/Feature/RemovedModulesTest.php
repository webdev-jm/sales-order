<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;

use App\Http\Livewire\Coe\Form;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Pre Plan, PAF, Productivity Report, DMS (districts and territories), RUD (credit memos and
 * CM reasons), Invoice and Purchase Orders were removed from the app. Their tables are kept.
 */
class RemovedModulesTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function removedRouteNames(): array
    {
        return [
            'paf' => ['paf.index'],
            'pre plan' => ['pre-plan.index'],
            'productivity report' => ['productivity-report.index'],
            'district' => ['district.index'],
            'territory' => ['territory.index'],
            'credit memo' => ['cm.index'],
            'cm reason' => ['cm-reason.index'],
            'invoice' => ['invoice.index'],
            'purchase order' => ['purchase-order.index'],
        ];
    }

    #[DataProvider('removedRouteNames')]
    public function test_the_module_route_is_gone(string $name): void
    {
        $this->assertFalse(Route::has($name));
    }

    public function test_invoice_terms_are_kept(): void
    {
        $this->assertTrue(Route::has('invoice-term.index'));
    }

    public function test_the_menu_no_longer_links_to_removed_modules(): void
    {
        $urls = [];
        $walk = function (array $items) use (&$walk, &$urls): void {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                if (isset($item['url'])) {
                    $urls[] = ltrim($item['url'], '/');
                }
                if (isset($item['submenu'])) {
                    $walk($item['submenu']);
                }
            }
        };
        $walk(config('adminlte.menu'));

        foreach (['paf', 'pre-plan', 'productivity-report', 'district', 'territory', 'credit-memo', 'cm-reason', 'invoice', 'purchase-order'] as $removed) {
            $this->assertNotContains($removed, $urls);
        }
        $this->assertContains('invoice-term', $urls);
    }

    public function test_the_sto_online_connection_is_gone(): void
    {
        $this->assertNull(config('database.connections.sto_online_db'));
    }

    public function test_the_sales_order_screens_no_longer_carry_the_paf_line_modal(): void
    {
        foreach (['create', 'edit'] as $page) {
            $source = file_get_contents(resource_path("views/pages/sales-orders/{$page}.blade.php"));

            $this->assertStringNotContainsString('sales-order-paf', $source);
            $this->assertStringNotContainsString('btn-paf-details', $source);
            $this->assertStringContainsString("Form::text('paf_number'", $source, 'The free-text PAF number on the order header stays.');
        }
    }

    public function test_coe_trade_marketing_takes_a_free_text_paf_number(): void
    {
        $component = $this->coeAtTradeMarketing(['paf_number' => ' 2026-a-00012 ', 'remarks' => 'Gondola end display']);

        $component->next();

        $this->assertSame(5, $component->stage);
        $this->assertSame('2026-A-00012', $component->trade_marketing_activities['paf_number']);
    }

    public function test_coe_trade_marketing_saves_a_blank_paf_number_as_none(): void
    {
        $component = $this->coeAtTradeMarketing(['paf_number' => '', 'remarks' => 'No program this week']);

        $component->next();

        $this->assertSame('NONE', $component->trade_marketing_activities['paf_number']);
    }

    public function test_coe_trade_marketing_still_needs_remarks(): void
    {
        $component = $this->coeAtTradeMarketing(['paf_number' => '2026-A-00012', 'remarks' => '']);

        $this->expectException(ValidationException::class);

        $component->next();
    }

    /**
     * @param array{paf_number: string, remarks: string} $trade_marketing_activities
     */
    private function coeAtTradeMarketing(array $trade_marketing_activities): Form
    {
        $component = new Form('coe-test');
        $component->logged_branch = (object) ['id' => 0];
        $component->stage = 4;
        $component->trade_marketing_activities = $trade_marketing_activities;

        return $component;
    }
}
