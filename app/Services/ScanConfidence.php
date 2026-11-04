<?php

namespace App\Services;

use App\Models\PurchaseOrderNumber;
use App\Models\SalesOrder;
use Illuminate\Support\Carbon;

/**
 * How sure the app is about each value the AI read from a PO PDF.
 *
 * The AI rates every field and line itself, but models tend to rate themselves high, so each value is also
 * checked against what the app knows: the product catalog, the account's prices, the arithmetic, the unit, the
 * dates, the account's shipping addresses and the PO numbers already used. A value takes the lowest of those
 * scores, so one failed check is enough to flag it. Low values must be checked by the user before the sales
 * order is created.
 */
final class ScanConfidence
{
    public const HIGH = 90;

    public const LOW = 70;

    /**
     * Score used when the AI gave no rating (scans cached before ratings existed).
     */
    private const UNRATED = 85;

    public const LEVELS = ['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'];

    /** PO price ÷ SMS price within this range is accepted as the same price (VAT, discounts, rounding). */
    private const PRICE_FAR_OFF = [0.5, 2.0];

    /** PO price ÷ SMS price within this range is taken to mean the PO priced that unit. */
    private const PRICE_FITS = [0.75, 1.35];

    public static function level(int $score): string
    {
        return $score >= self::HIGH ? 'high' : ($score >= self::LOW ? 'medium' : 'low');
    }

    /**
     * Rates one scanned PO. Lines the user left out are not rated and do not count.
     *
     * @param array<string, mixed> $order one entry of SalesOrderPdfReader::read()
     * @param array<int, bool> $included line index => kept on the order (all kept when missing)
     * @return array{
     *     fields: array<string, array{label: string, value: string, score: int, level: string, reasons: array<int, string>, find: array<int, string>, page: ?int}>,
     *     lines: array<int, array{score: int, level: string, reasons: array<int, string>}>,
     *     score: int,
     *     level: string,
     *     low: int
     * }
     */
    public static function assess(array $order, ?int $companyId, array $included = []): array
    {
        $header = $order['header'];
        $rating = fn (string $key) => $header['confidence'][$key] ?? self::UNRATED;
        $fields = [];

        $fields['po_number'] = self::field('PO number', $order['po_number'], $rating('po_number'), [$order['po_number']], $header['page']);
        if ($order['po_number'] === '') {
            self::cap($fields['po_number'], 0, 'No PO number was found on the document.');
        } elseif (self::poNumberUsed($order['po_number'], $companyId)) {
            self::cap($fields['po_number'], 20, 'This PO number is already on another sales order, so the order cannot be saved with it.');
        }

        $fields['po_date'] = self::field('PO date', $header['po_date'], $rating('po_date'), self::dateVariants($header['po_date']), $header['page']);
        if ($header['po_date'] !== '' && !self::isDate($header['po_date'])) {
            self::cap($fields['po_date'], 50, 'The PO date could not be read as a date.');
        }

        $shipDate = (string) ($order['ship_date'] ?? '');
        $fields['ship_date'] = self::field('Ship date', $shipDate, $rating('delivery_date'), self::dateVariants($header['delivery_date_printed']), $header['page']);
        if (!empty($order['ship_date_error'])) {
            self::cap($fields['ship_date'], 50, $order['ship_date_error']);
        } elseif ($shipDate !== '' && self::isDate($header['po_date']) && Carbon::parse($shipDate)->lt(Carbon::parse($header['po_date']))) {
            self::cap($fields['ship_date'], 60, 'The ship date is before the PO date.');
        } elseif ($shipDate !== '' && Carbon::parse($shipDate)->lt(today())) {
            self::cap($fields['ship_date'], 75, 'The ship date is in the past.');
        }

        $store = trim($header['store_code'] . ' ' . $header['store_name']);
        $fields['ship_to'] = self::field('Ship-to', $order['shipping_address_id'] ? trim(trim($order['address_code']) . ' ' . $header['store_name']) : ($store !== '' ? $store : 'Account address'), $rating('store'), [$header['store_code'], $header['store_name']], $header['page']);
        if ($store !== '' && empty($order['shipping_address_id'])) {
            self::cap($fields['ship_to'], 50, 'Store ' . $store . ' is not one of the account\'s shipping addresses, so the account address is used.');
        } elseif ($store === '') {
            self::cap($fields['ship_to'], 85, 'No ship-to store is printed, so the account address is used.');
        }

        $lines = [];
        $sum = 0.0;
        $withoutQuantity = 0;
        foreach ($order['lines'] as $index => $line) {
            if (($included[$index] ?? true) === false) {
                continue;
            }
            $lines[$index] = self::line($line);
            if (is_numeric($line['amount'])) {
                $sum += (float) $line['amount'];
            }
            $withoutQuantity += (float) $line['quantity'] <= 0 ? 1 : 0;
        }

        $printedTotal = $header['printed_total'];
        $fields['total'] = self::field('PO total', is_numeric($printedTotal) ? number_format((float) $printedTotal, 2) : number_format($sum, 2), $rating('total'), [is_numeric($printedTotal) ? number_format((float) $printedTotal, 2) : ''], $header['page']);
        if (!is_numeric($printedTotal)) {
            self::cap($fields['total'], 85, 'No total is printed; this is the sum of the lines.');
        } elseif (abs((float) $printedTotal - $sum) > 1 && !\in_array(false, $included, true)) {
            self::cap($fields['total'], 75, 'The lines add up to ' . number_format($sum, 2) . ', not the printed ' . number_format((float) $printedTotal, 2) . '.'
                . ($withoutQuantity > 0 ? ' ' . $withoutQuantity . ($withoutQuantity === 1 ? ' line has' : ' lines have') . ' no quantity.' : ''));
        }

        $scores = array_merge(array_column($fields, 'score'), array_column($lines, 'score'));
        $score = $scores === [] ? 0 : min($scores);
        $low = count(array_filter($scores, fn (int $s) => $s < self::LOW));

        return ['fields' => $fields, 'lines' => $lines, 'score' => $score, 'level' => self::level($score), 'low' => $low];
    }

    /**
     * @param array<string, mixed> $line
     * @return array{score: int, level: string, reasons: array<int, string>}
     */
    private static function line(array $line): array
    {
        $rated = ['score' => $line['ai_confidence'] ?? self::UNRATED, 'reasons' => []];
        if ($line['ai_unclear'] !== '') {
            $rated['reasons'][] = 'The AI noted: ' . rtrim($line['ai_unclear'], '.') . '.';
        }
        if ($line['ai_confidence'] !== null && $line['ai_confidence'] < self::HIGH && $line['ai_unclear'] === '') {
            $rated['reasons'][] = 'The AI was not fully sure it read this line correctly.';
        }

        if (empty($line['product_id'])) {
            self::cap($rated, 40, $line['sku'] !== ''
                ? 'No product for item code ' . $line['sku'] . ' on this account, so this line cannot be ordered.'
                : 'No item code was read for this line, so it cannot be ordered.');
        }

        if (!empty($line['product_id']) && filled($line['sms_problem'] ?? null)) {
            self::cap($rated, 40, $line['sms_problem']);
        }

        $quantity = (float) $line['quantity'];
        if ($quantity <= 0) {
            self::cap($rated, 40, 'The quantity is zero or missing.');
        }

        if (!empty($line['product_id']) && ($priceProblem = self::priceProblem($line, $quantity)) !== null) {
            self::cap($rated, 65, $priceProblem);
        }

        if ($line['uom'] === '') {
            self::cap($rated, 85, 'No unit is printed, so ' . $line['so_uom'] . ' was used.');
        }

        if (isset($line['read_quantity'])) {
            $rated['reasons'][] = 'You changed the quantity from ' . self::number((float) $line['read_quantity']) . ' to ' . self::number($quantity) . '.';
        } elseif (is_numeric($line['unit_price']) && is_numeric($line['amount']) && (float) $line['unit_price'] > 0) {
            $expected = $quantity * (float) $line['unit_price'];
            $amount = (float) $line['amount'];
            if (abs($expected - $amount) > max(1, $amount * 0.005)) {
                $reason = self::number($quantity) . ' × ' . number_format((float) $line['unit_price'], 2) . ' is ' . number_format($expected, 2) . ', but the PO shows ' . number_format($amount, 2) . '.';
                $fits = $amount / (float) $line['unit_price'];
                if (abs($fits - round($fits)) < 0.001 && round($fits) > 0 && round($fits) != $quantity) {
                    $reason .= ' A quantity of ' . self::number(round($fits)) . ' would match.';
                }
                self::cap($rated, 65, $reason);
            }
        }

        return ['score' => (int) $rated['score'], 'level' => self::level((int) $rated['score']), 'reasons' => $rated['reasons']];
    }

    /**
     * @param array<int, string> $find text printed on the PDF that the preview searches for
     * @return array{label: string, value: string, score: int, level: string, reasons: array<int, string>, find: array<int, string>, page: ?int}
     */
    private static function field(string $label, string $value, int $score, array $find, ?int $page): array
    {
        return ['label' => $label, 'value' => $value, 'score' => $score, 'level' => self::level($score), 'reasons' => [], 'find' => array_values(array_filter($find, fn ($text) => trim((string) $text) !== '')), 'page' => $page];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function cap(array &$item, int $max, string $reason): void
    {
        $item['score'] = min($item['score'], $max);
        $item['reasons'][] = $reason;
        if (isset($item['level'])) {
            $item['level'] = self::level($item['score']);
        }
    }

    /**
     * Why the PO's unit price does not fit what SMS charges for the line's product and unit, or null when it
     * fits or cannot be compared. PO prices often include VAT or the account's discounts, so only a price that
     * is far off counts; one that fits another unit of the product points to the wrong unit.
     *
     * @param array<string, mixed> $line
     */
    private static function priceProblem(array $line, float $quantity): ?string
    {
        $smsPrices = (array) ($line['sms_prices'] ?? []);
        $uom = (string) $line['so_uom'];

        $poPrice = is_numeric($line['unit_price']) ? (float) $line['unit_price'] : 0.0;
        if ($poPrice <= 0 && !isset($line['read_quantity']) && is_numeric($line['amount']) && $quantity > 0) {
            $poPrice = (float) $line['amount'] / $quantity;
        }

        if ($poPrice <= 0 || empty($smsPrices[$uom])) {
            return null;
        }

        $fits = fn (float $smsPrice): bool => $poPrice / $smsPrice >= self::PRICE_FITS[0] && $poPrice / $smsPrice <= self::PRICE_FITS[1];
        $ratio = $poPrice / (float) $smsPrices[$uom];

        if ($ratio >= self::PRICE_FAR_OFF[0] && $ratio <= self::PRICE_FAR_OFF[1]) {
            return null;
        }

        foreach ($smsPrices as $otherUom => $smsPrice) {
            if ($otherUom !== $uom && (float) $smsPrice > 0 && $fits((float) $smsPrice)) {
                return 'The PO price ' . number_format($poPrice, 2) . ' fits ' . $otherUom . ' (SMS price ' . number_format((float) $smsPrice, 2)
                    . '), not ' . $uom . ' (' . number_format((float) $smsPrices[$uom], 2) . '). Check the unit.';
            }
        }

        return 'The PO price ' . number_format($poPrice, 2) . ' is far from the SMS price of ' . number_format((float) $smsPrices[$uom], 2)
            . ' per ' . $uom . '. Check that this is the right product and unit.';
    }

    private static function poNumberUsed(string $poNumber, ?int $companyId): bool
    {
        return SalesOrder::where('po_number', $poNumber)->where('status', '!=', 'cancelled')->exists()
            || ($companyId !== null && PurchaseOrderNumber::where('po_number', $poNumber)->where('company_id', $companyId)->exists());
    }

    /**
     * The ways a date is commonly printed, so the preview can find it on the page.
     *
     * @return array<int, string>
     */
    private static function dateVariants(string $value): array
    {
        if (!self::isDate($value)) {
            return [$value];
        }

        $date = Carbon::parse($value);

        return array_values(array_unique([$value, $date->format('m/d/Y'), $date->format('n/j/Y'), $date->format('m/d/y'), $date->format('d/m/Y'), $date->format('d-M-Y'), $date->format('M d, Y'), $date->format('d.m.Y')]));
    }

    private static function isDate(string $value): bool
    {
        return $value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && strtotime($value) !== false;
    }

    private static function number(float $value): string
    {
        return floor($value) == $value ? number_format($value) : rtrim(rtrim(number_format($value, 4), '0'), '.');
    }
}
