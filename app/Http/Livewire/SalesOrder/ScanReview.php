<?php

namespace App\Http\Livewire\SalesOrder;

use App\Models\Account;
use App\Services\SalesOrderAttachmentService;
use App\Services\SalesOrderPdfReader;
use App\Services\SalesOrderScanHandoff;
use App\Services\ScanConfidence;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

/**
 * Review of a PO PDF read with AI, before the Create SO form is filled: the PDF beside what was read,
 * with a confidence level on every value. Low values must be checked before the order can be created.
 *
 * The scan itself sits in the session (SalesOrderScanHandoff::REVIEW_SESSION_KEY), so only the user's
 * choices travel with each request.
 */
class ScanReview extends Component
{
    /** Position of the PO being reviewed among the POs in the PDF. */
    public int $po = 0;

    /** @var array<int, array<int, bool>> PO position => line index => kept on the order */
    public array $included = [];

    /** @var array<int, array<int, mixed>> PO position => line index => quantity to order */
    public array $quantities = [];

    /** @var array<int, array<string, bool>> PO position => "field-<key>" or "line-<index>" => checked by the user */
    public array $confirmed = [];

    /** @var array<int, string> PO position => ship date */
    public array $shipDates = [];

    public function mount(): void
    {
        $this->authorizeReview();

        foreach ($this->orders() as $poNumber => $order) {
            $this->included[$poNumber] = array_fill_keys(array_keys($order['lines']), true);
            $this->quantities[$poNumber] = array_map(fn (array $line) => $this->displayNumber($line['quantity']), $order['lines']);
            $this->confirmed[$poNumber] = [];
            $this->shipDates[$poNumber] = (string) ($order['ship_date'] ?? '');
        }

        $this->po = 0;
    }

    public function selectPo(int $poNumber): void
    {
        if (array_key_exists($poNumber, $this->orders())) {
            $this->po = $poNumber;
            $this->resetErrorBag();
        }
    }

    public function createSalesOrder()
    {
        $this->authorizeReview();

        $order = $this->reviewedOrder();
        $confidence = $this->confidence();

        if ($this->unchecked($confidence) > 0) {
            $this->addError('review', 'Check the values marked Low first: tick "I checked this" once you have compared each one with the PDF.');

            return null;
        }

        $items = SalesOrderPdfReader::itemsFrom($order['lines'], $this->included[$this->po] ?? [], $this->quantities[$this->po] ?? []);

        if ($items === []) {
            $this->addError('review', 'No line can be ordered. Keep at least one line that has a matched product and a quantity.');

            return null;
        }

        $account = $this->account();
        $handoff = app(SalesOrderScanHandoff::class)->prepare($order, $items, $account, $this->shipDates[$this->po] ?? null);

        $attachments = app(SalesOrderAttachmentService::class);
        $attachments->setPendingScan(SalesOrderAttachmentService::scanRecord(
            $order,
            $confidence,
            (string) ($this->review()['file_name'] ?? ''),
            $this->included[$this->po] ?? [],
            $this->quantities[$this->po] ?? [],
            $this->confirmed[$this->po] ?? []
        ));

        $flash = $handoff['flash'];
        if (count($this->orders()) > 1) {
            $flash['scan_notices'][] = 'The PDF holds ' . count($this->orders()) . ' purchase orders. PO ' . $order['po_number']
                . ' was loaded; use Upload Multiple SO to create the others.';
        }

        Session::put('order_data', $handoff['order_data']);
        Session::forget(SalesOrderScanHandoff::REVIEW_SESSION_KEY);

        return redirect()->route('sales-order.create')->with($flash);
    }

    public function discard()
    {
        $this->authorizeReview();

        app(SalesOrderAttachmentService::class)->discardPending();
        Session::forget(SalesOrderScanHandoff::REVIEW_SESSION_KEY);

        return redirect()->route('sales-order.create')->with('message_success', 'The scanned PO was discarded.');
    }

    public function render()
    {
        $orders = $this->orders();
        $order = $this->reviewedOrder();
        $confidence = $this->confidence();

        return view('livewire.sales-order.scan-review')->with([
            'fileName'   => (string) ($this->review()['file_name'] ?? ''),
            'orders'     => $orders,
            'order'      => $order,
            'confidence' => $confidence,
            'unchecked'  => $this->unchecked($confidence),
            'levels'     => ScanConfidence::LEVELS,
            'poLevels'   => collect($orders)->map(fn (array $o) => $o['confidence']['level'] ?? 'medium')->all(),
        ]);
    }

    /**
     * The selected PO with the user's quantities and ship date applied.
     *
     * @return array<string, mixed>
     */
    private function reviewedOrder(): array
    {
        $orders = $this->orders();
        $order = $orders[$this->po] ?? reset($orders);

        foreach ($order['lines'] as $index => $line) {
            $quantity = $this->quantities[$this->po][$index] ?? null;
            $order['lines'][$index]['quantity'] = is_numeric($quantity) ? (float) $quantity : 0.0;
            if (abs($order['lines'][$index]['quantity'] - (float) $line['quantity']) > 0.00001) {
                $order['lines'][$index]['read_quantity'] = (float) $line['quantity'];
            }
        }

        $shipDate = $this->shipDates[$this->po] ?? '';
        if ($shipDate !== (string) ($order['ship_date'] ?? '')) {
            $order['ship_date'] = $shipDate !== '' ? $shipDate : null;
            $order['ship_date_error'] = $shipDate === '' ? 'Enter the ship date.' : null;
        }

        return $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function confidence(): array
    {
        return ScanConfidence::assess($this->reviewedOrder(), $this->account()->company_id, $this->included[$this->po] ?? []);
    }

    /**
     * Low values the user has not ticked yet.
     *
     * @param array<string, mixed> $confidence
     */
    private function unchecked(array $confidence): int
    {
        $confirmed = $this->confirmed[$this->po] ?? [];
        $open = 0;

        foreach ($confidence['fields'] as $key => $field) {
            $open += ($field['level'] === 'low' && empty($confirmed['field-' . $key])) ? 1 : 0;
        }
        foreach ($confidence['lines'] as $index => $line) {
            $open += ($line['level'] === 'low' && empty($confirmed['line-' . $index])) ? 1 : 0;
        }

        return $open;
    }

    /**
     * @return array<string, mixed>
     */
    private function review(): array
    {
        $review = Session::get(SalesOrderScanHandoff::REVIEW_SESSION_KEY);

        abort_unless(\is_array($review) && !empty($review['orders']), 404);

        return $review;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function orders(): array
    {
        return array_values($this->review()['orders']);
    }

    private function account(): Account
    {
        return Account::findOrFail($this->review()['account_id']);
    }

    private function authorizeReview(): void
    {
        abort_unless(auth()->user()?->can('sales order create') && auth()->user()->can('po scan access'), 403);
    }

    private function displayNumber(float $value): string
    {
        return floor($value) == $value ? (string) (int) $value : (string) $value;
    }
}
