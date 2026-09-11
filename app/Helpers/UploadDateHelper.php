<?php

namespace App\Helpers;

use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Single entry point for every date read out of an uploaded file.
 *
 * Uploads reach us from spreadsheets the users maintain themselves, so a date
 * column holds whatever their locale and Excel settings produced: a real date
 * object, an Excel serial number, or free text typed in any of a dozen shapes.
 * Each upload used to carry its own one-format parser, which silently stored
 * an unusable value - or nothing - whenever the sheet disagreed with it.
 *
 * `parse()` accepts every shape we have seen and normalizes it to `Y-m-d`;
 * `error()` turns anything it cannot resolve into a message the user can act on.
 */
class UploadDateHelper
{
    /**
     * Text formats we accept, US-style first to match the templates this
     * application ships. Order matters: the first format that consumes the
     * whole value without warnings wins.
     *
     * @var string[]
     */
    private const TEXT_FORMATS = [
        'Y-m-d',
        'Y/m/d',
        'm-d-Y',
        'm/d/Y',
        'm.d.Y',
        'd-m-Y',
        'd/m/Y',
        'm-d-y',
        'm/d/y',
        'd-M-Y',
        'd-M-y',
        'd M Y',
        'M d, Y',
        'M d Y',
        'F d, Y',
        'F d Y',
        'Y-m-d H:i:s',
        'Y-m-d H:i',
        'Y-m-d\TH:i:s',
        'Y-m-d\TH:i:sP',
        'm/d/Y H:i:s',
        'm/d/Y H:i',
        'm-d-Y H:i:s',
        'm-d-Y H:i',
    ];

    /**
     * Normalize an uploaded cell value into a `Y-m-d` string.
     *
     * A date cell reaches us in one of three shapes: a real date object, an
     * Excel serial number (int, float, or a numeric string - a formula result
     * is always a float, so an `is_int()` check alone misses most of them), or
     * free text the user typed in whatever format they are used to. Anything
     * that cannot be resolved returns null so the caller can flag the row
     * instead of storing an unusable value.
     */
    public static function parse(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '' || is_bool($value)) {
            return null;
        }

        if (is_string($value) && preg_match('/^(19|20)\d{6}$/', $value)) {
            return self::parseText($value, ['Ymd']);
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            return self::parseExcelSerial((float) $value);
        }

        if (!is_string($value)) {
            return null;
        }

        return self::parseText($value);
    }

    /**
     * Whether the cell holds no value at all, as opposed to a value we could
     * not read as a date. The two cases need different messages.
     */
    public static function isBlank(mixed $value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        return $value === null || $value === '' || $value === [];
    }

    /**
     * The message to show for a date cell, or null when the cell is fine.
     *
     * @param  string  $label     how the column is named to the user, e.g. "Ship date"
     * @param  bool    $required  whether an empty cell is itself an error
     */
    public static function error(mixed $value, string $label, bool $required = true): ?string
    {
        if (self::isBlank($value)) {
            return $required ? $label.' is required.' : null;
        }

        if (self::parse($value) !== null) {
            return null;
        }

        return $label.' is not a valid date'.self::quote($value)
            .'. Use a date such as 01/31/'.date('Y').', 2025-01-31 or 31-Jan-2025.';
    }

    /**
     * Fold a list of per-row messages into one line for a flash message.
     *
     * A sheet with a misformatted date column produces one message per row, and
     * showing hundreds of them helps nobody - the first few are enough to find
     * the problem, so the rest are counted instead.
     *
     * @param  string[]  $messages
     */
    public static function summarizeErrors(array $messages, int $limit = 5): string
    {
        $messages = array_values(array_filter($messages));

        if ($messages === []) {
            return '';
        }

        $shown = array_slice($messages, 0, $limit);
        $summary = implode(' ', $shown);

        $remaining = count($messages) - count($shown);
        if ($remaining > 0) {
            $summary .= ' (and '.$remaining.' more row'.($remaining > 1 ? 's' : '').').';
        }

        return $summary;
    }

    /**
     * Render the offending value inside the message so the user can find the
     * cell, keeping it short enough not to swamp the message.
     */
    private static function quote(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return '';
        }

        if (is_bool($value)) {
            $value = $value ? 'TRUE' : 'FALSE';
        } elseif (is_array($value) || is_object($value)) {
            return '';
        }

        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) > 30) {
            $value = mb_substr($value, 0, 30).'...';
        }

        return ' ("'.$value.'")';
    }

    /**
     * Convert an Excel serial number to `Y-m-d`.
     *
     * The plausibility check keeps a stray quantity or amount that landed in a
     * date column from being converted into a nonsense date.
     */
    private static function parseExcelSerial(float $serial): ?string
    {
        if ($serial < 1 || $serial > 2958465) {
            return null;
        }

        try {
            $date = ExcelDate::excelToDateTimeObject($serial);
        } catch (\Throwable $e) {
            return null;
        }

        return self::isPlausibleYear((int) $date->format('Y')) ? $date->format('Y-m-d') : null;
    }

    /**
     * Try each supported text format in turn.
     *
     * @param  string[]|null  $formats
     */
    private static function parseText(string $value, ?array $formats = null): ?string
    {
        $formats = $formats ?? self::TEXT_FORMATS;

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!'.$format, $value);
            $errors = \DateTime::getLastErrors() ?: [];

            if ($date === false || !empty($errors['error_count']) || !empty($errors['warning_count'])) {
                continue;
            }

            if (self::isPlausibleYear((int) $date->format('Y'))) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Every date this application uploads is a current business date, so a year
     * outside this window means the value was misread - a two-digit year taken
     * literally by a `Y` token, or a quantity sitting in a date column.
     */
    private static function isPlausibleYear(int $year): bool
    {
        return $year >= 1990 && $year <= 2100;
    }
}
