<?php

namespace Tests\Unit;

use App\Http\Traits\GlobalTrait;
use PHPUnit\Framework\TestCase;

/**
 * Upload sheets carry dates in whatever shape Excel and the user leave behind:
 * serial numbers (int, float, or numeric string) and a handful of typed text
 * formats. The old parser only understood whole-number serials and `m-d-Y`,
 * so everything else landed in the form as an empty date field.
 */
class SpreadsheetDateParsingTest extends TestCase
{
    use GlobalTrait;

    /**
     * @return array<string, array{0: mixed, 1: ?string}>
     */
    public static function dateProvider(): array
    {
        return [
            'excel serial as int'          => [45678, '2025-01-21'],
            'excel serial as float'        => [45678.0, '2025-01-21'],
            'excel serial with time'       => [45678.75, '2025-01-21'],
            'excel serial as string'       => ['45678', '2025-01-21'],
            'iso text'                     => ['2025-01-16', '2025-01-16'],
            'iso text with time'           => ['2025-01-16 08:30:00', '2025-01-16'],
            'us dashes'                    => ['01-16-2025', '2025-01-16'],
            'us dashes unpadded'           => ['1-6-2025', '2025-01-06'],
            'us slashes'                   => ['01/16/2025', '2025-01-16'],
            'us slashes unpadded'          => ['1/6/2025', '2025-01-06'],
            'us slashes two digit year'    => ['01/16/25', '2025-01-16'],
            'us dashes two digit year'     => ['01-16-25', '2025-01-16'],
            'day first when month cannot'  => ['16/01/2025', '2025-01-16'],
            'abbreviated month'            => ['16-Jan-2025', '2025-01-16'],
            'long month with comma'        => ['January 16, 2025', '2025-01-16'],
            'abbreviated month with comma' => ['Jan 16, 2025', '2025-01-16'],
            'compact numeric date'         => ['20250116', '2025-01-16'],
            'padded with spaces'           => ['  01/16/2025  ', '2025-01-16'],
            'datetime object'              => [new \DateTime('2025-01-16 13:45:00'), '2025-01-16'],
            'null'                         => [null, null],
            'empty string'                 => ['', null],
            'whitespace only'              => ['   ', null],
            'plain text'                   => ['not a date', null],
            'quantity in a date column'    => [12, null],
            'amount in a date column'      => [1500.5, null],
            'impossible calendar date'     => ['02/31/2025', null],
        ];
    }

    /**
     * @dataProvider dateProvider
     *
     * @param  mixed  $value
     */
    public function test_parse_spreadsheet_date_normalizes_every_supported_shape($value, ?string $expected): void
    {
        $this->assertSame($expected, $this->parseSpreadsheetDate($value));
    }

    /**
     * A time component must never roll the date forward or backward, and the
     * result must not pick up "now" for the missing parts.
     */
    public function test_time_component_does_not_shift_the_day(): void
    {
        $this->assertSame('2025-01-16', $this->parseSpreadsheetDate('01/16/2025 23:59'));
        $this->assertSame('2025-01-16', $this->parseSpreadsheetDate('2025-01-16 00:00:00'));
    }

    /**
     * Serial numbers outside the Excel calendar are data in the wrong column,
     * not dates.
     */
    public function test_out_of_range_serials_are_rejected(): void
    {
        $this->assertNull($this->parseSpreadsheetDate(0));
        $this->assertNull($this->parseSpreadsheetDate(-5));
        $this->assertNull($this->parseSpreadsheetDate(3000000));
    }
}
