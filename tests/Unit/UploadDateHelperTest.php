<?php

namespace Tests\Unit;

use App\Helpers\UploadDateHelper;
use PHPUnit\Framework\TestCase;

/**
 * Every upload now reads its date columns through this helper, so it has to
 * accept the shapes a user's spreadsheet can produce and explain - rather than
 * silently drop - the ones it cannot.
 */
class UploadDateHelperTest extends TestCase
{
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
            'us slashes unpadded'          => ['1/6/2025', '2025-01-06'],
            'us slashes two digit year'    => ['01/16/25', '2025-01-16'],
            'day first when month cannot'  => ['16/01/2025', '2025-01-16'],
            'abbreviated month'            => ['16-Jan-2025', '2025-01-16'],
            'long month with comma'        => ['January 16, 2025', '2025-01-16'],
            'compact numeric date'         => ['20250116', '2025-01-16'],
            'padded with spaces'           => ['  01/16/2025  ', '2025-01-16'],
            'datetime object'              => [new \DateTime('2025-01-16 13:45:00'), '2025-01-16'],
            'null'                         => [null, null],
            'empty string'                 => ['', null],
            'plain text'                   => ['not a date', null],
            'quantity in a date column'    => [12, null],
            'impossible calendar date'     => ['02/31/2025', null],
        ];
    }

    /**
     * @dataProvider dateProvider
     *
     * @param  mixed  $value
     */
    public function test_parse_normalizes_every_supported_shape($value, ?string $expected): void
    {
        $this->assertSame($expected, UploadDateHelper::parse($value));
    }

    public function test_a_readable_date_produces_no_message(): void
    {
        $this->assertNull(UploadDateHelper::error('01/16/2025', 'Ship date'));
        $this->assertNull(UploadDateHelper::error(45678, 'Ship date'));
        $this->assertNull(UploadDateHelper::error(new \DateTime('2025-01-16'), 'Ship date'));
    }

    /**
     * The message has to name the column and quote the cell, otherwise the user
     * cannot find the row to fix in a sheet of hundreds.
     */
    public function test_an_unreadable_value_is_reported_with_its_column_and_value(): void
    {
        $message = UploadDateHelper::error('next tuesday', 'Ship date');

        $this->assertNotNull($message);
        $this->assertStringContainsString('Ship date', $message);
        $this->assertStringContainsString('is not a valid date', $message);
        $this->assertStringContainsString('next tuesday', $message);
    }

    public function test_a_quantity_sitting_in_a_date_column_is_reported(): void
    {
        $message = UploadDateHelper::error(12, 'Schedule date');

        $this->assertNotNull($message);
        $this->assertStringContainsString('is not a valid date', $message);
    }

    public function test_an_empty_cell_is_reported_as_missing_not_as_invalid(): void
    {
        $message = UploadDateHelper::error('   ', 'Pick-up date');

        $this->assertSame('Pick-up date is required.', $message);
    }

    public function test_an_empty_cell_is_accepted_when_the_column_is_optional(): void
    {
        $this->assertNull(UploadDateHelper::error(null, 'RTV date', false));
        $this->assertNull(UploadDateHelper::error('', 'RTV date', false));
    }

    /**
     * An optional column still has to reject junk - it is optional, not free text.
     */
    public function test_an_optional_column_still_rejects_an_unreadable_value(): void
    {
        $this->assertNotNull(UploadDateHelper::error('lorem ipsum', 'RTV date', false));
    }

    public function test_a_very_long_value_is_truncated_in_the_message(): void
    {
        $message = UploadDateHelper::error(str_repeat('x', 80), 'Start date');

        $this->assertStringContainsString('...', $message);
        $this->assertLessThan(200, strlen($message));
    }

    public function test_blank_detection_separates_empty_cells_from_bad_values(): void
    {
        $this->assertTrue(UploadDateHelper::isBlank(null));
        $this->assertTrue(UploadDateHelper::isBlank(''));
        $this->assertTrue(UploadDateHelper::isBlank('   '));
        $this->assertFalse(UploadDateHelper::isBlank('not a date'));
        $this->assertFalse(UploadDateHelper::isBlank(0));
    }

    public function test_row_messages_are_summarized_with_a_count_of_the_rest(): void
    {
        $messages = array_map(fn(int $row) => 'Row '.$row.': Start date is required.', range(2, 9));

        $summary = UploadDateHelper::summarizeErrors($messages, 3);

        $this->assertStringContainsString('Row 2:', $summary);
        $this->assertStringContainsString('Row 4:', $summary);
        $this->assertStringNotContainsString('Row 5:', $summary);
        $this->assertStringContainsString('and 5 more rows', $summary);
    }

    public function test_summarizing_nothing_produces_an_empty_string(): void
    {
        $this->assertSame('', UploadDateHelper::summarizeErrors([]));
    }
}
