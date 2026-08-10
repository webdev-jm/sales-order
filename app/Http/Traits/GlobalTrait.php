<?php

namespace App\Http\Traits;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

trait GlobalTrait {

    /**
     * Retrieve application settings, cached for 60 seconds.
     *
     * The settings row changes rarely (admin-only edits), so a 60-second TTL
     * eliminates the per-request DB hit in every controller constructor while
     * still propagating changes within a minute.
     */
    public function getSettings()
    {
        return Cache::remember('app_settings', 60, fn() => Setting::first());
    }

    /**
     * Normalize a spreadsheet cell value into a `Y-m-d` string.
     *
     * A date cell reaches us in one of three shapes: a real date object, an
     * Excel serial number (int, float, or a numeric string - a formula result
     * is always a float, so an `is_int()` check alone misses most of them), or
     * free text the user typed in whatever format they are used to. Anything
     * that cannot be resolved returns null so the caller can flag the row
     * instead of storing an unusable value.
     *
     * @param  mixed  $value
     */
    public function parseSpreadsheetDate($value): ?string
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
            return $this->parseDateString($value, ['Ymd']);
        }

        if (is_int($value) || is_float($value) || (is_string($value) && is_numeric($value))) {
            return $this->parseExcelSerial((float) $value);
        }

        if (!is_string($value)) {
            return null;
        }

        return $this->parseDateString($value);
    }

    /**
     * Convert an Excel serial number to `Y-m-d`.
     *
     * The plausibility check keeps a stray quantity or amount that landed in a
     * date column from being converted into a nonsense date.
     */
    private function parseExcelSerial(float $serial): ?string
    {
        if ($serial < 1 || $serial > 2958465) {
            return null;
        }

        try {
            $date = ExcelDate::excelToDateTimeObject($serial);
        } catch (\Throwable $e) {
            return null;
        }

        return $this->isPlausibleYear((int) $date->format('Y')) ? $date->format('Y-m-d') : null;
    }

    /**
     * Try each supported text format in turn, US-style first to match the
     * templates this application ships.
     *
     * @param  string[]|null  $formats
     */
    private function parseDateString(string $value, ?array $formats = null): ?string
    {
        $formats = $formats ?? [
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

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat('!'.$format, $value);
            $errors = \DateTime::getLastErrors() ?: [];

            if ($date === false || !empty($errors['error_count']) || !empty($errors['warning_count'])) {
                continue;
            }

            if ($this->isPlausibleYear((int) $date->format('Y'))) {
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
    private function isPlausibleYear(int $year): bool
    {
        return $year >= 1990 && $year <= 2100;
    }
}
