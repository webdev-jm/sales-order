<?php

namespace Tests\Feature;

use App\Http\Requests\StoreSalesOrderRequest;
use App\Http\Requests\UpdateSalesOrderRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * PO numbers may contain a caret; accounts use it as a separator in their own
 * numbering. Everything outside letters, digits, spaces, hyphens and carets
 * stays rejected.
 */
class SalesOrderPoNumberValidationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<int, mixed> the po_number rules of the given request class
     */
    private function poNumberRules(string $requestClass): array
    {
        $this->actingAs(User::factory()->create());

        return (new $requestClass)->rules()['po_number'];
    }

    private function passes(string $requestClass, string $poNumber): bool
    {
        return Validator::make(
            ['po_number' => $poNumber],
            ['po_number' => $this->poNumberRules($requestClass)]
        )->passes();
    }

    public function test_store_accepts_a_caret_in_the_po_number(): void
    {
        $this->assertTrue($this->passes(StoreSalesOrderRequest::class, 'PO^12345'));
    }

    public function test_update_accepts_a_caret_in_the_po_number(): void
    {
        $this->assertTrue($this->passes(UpdateSalesOrderRequest::class, 'PO^12345'));
    }

    public function test_plain_po_numbers_are_still_accepted(): void
    {
        $this->assertTrue($this->passes(StoreSalesOrderRequest::class, 'PH-00123'));
    }

    /**
     * @dataProvider disallowedCharacters
     */
    public function test_other_special_characters_are_still_rejected(string $poNumber): void
    {
        $this->assertFalse($this->passes(StoreSalesOrderRequest::class, $poNumber));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function disallowedCharacters(): array
    {
        return [
            'hash'      => ['PO#12345'],
            'slash'     => ['PO/12345'],
            'ampersand' => ['PO&12345'],
            'quote'     => ["PO'12345"],
        ];
    }
}
