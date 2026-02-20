<?php

namespace App\Http\Traits;

use App\Helpers\XmlConverterHelper;
use Illuminate\Support\Facades\Http;

trait CreditMemoXml
{
    public $rud;
    protected $api_base_url = '192.168.11.240/refreshable/public/api/credit-memo/';

    /**
     * Generates and returns the required XML documents for Syspro Integration.
     *
     * @return array
     */
    public function generateCreditMemoXmls()
    {
        $xmlConverter = new XmlConverterHelper();
        $soNumber = $this->getFormattedSoNumber();

        // Fetch API details and determine if it's a full or partial credit
        $apiData = $this->getCmDetails();
        $isFullCredit = $this->checkIfFullCredit($apiData);

        // If all bins are selected, use SORTCI
        if ($isFullCredit) {
            return [
                'sortci_xml' => $xmlConverter->arrayToXml(
                    [$this->buildSortCiDocData($soNumber, $apiData)],
                    'PostCreditFromInvoice',
                    'SORTCIDOC.XSD'
                ),
            ];
        }
        // If not all bins are selected, use SORTCH and SORTCL
        else {
            return [
                'sortch_xml' => $xmlConverter->arrayToXml(
                    [$this->buildSortChDocData($soNumber)],
                    'SOCreditNoteHeader',
                    'SORTCHDOC.XSD'
                ),
                'sortcl_xml' => $xmlConverter->arrayToXml(
                    $this->buildSortClDocData($soNumber),
                    'SOCreditNoteLine',
                    'SORTCLDOC.XSD'
                ),
            ];
        }
    }

    /**
     * Compares the total bins from the API with the selected bins in the RUD details.
     * Returns true if all bins are selected.
     */
    private function checkIfFullCredit($apiData)
    {
        // Safety check if API returned empty details or RUD has no details
        if (empty($apiData['details']) || !$this->rud || !$this->rud->cm_details) {
            return false;
        }

        // 1. Get an array of the Stock Codes that exist in the RUD details
        $selectedStockCodes = [];
        foreach ($this->rud->cm_details as $detail) {
            if ($detail->product && $detail->product->stock_code) {
                $selectedStockCodes[] = $detail->product->stock_code;
            }
        }

        // 2. Count total bins available in the API response ONLY for the selected Stock Codes
        $apiBinCount = 0;
        foreach ($apiData['details'] as $line) {
            if (isset($line['StockCode']) && in_array($line['StockCode'], $selectedStockCodes)) {
                if (isset($line['bin_data']) && is_array($line['bin_data'])) {
                    $apiBinCount += count($line['bin_data']);
                }
            }
        }

        // 3. Count total bins currently selected and saved in the RUD object
        $rudBinCount = 0;
        foreach ($this->rud->cm_details as $detail) {
            if ($detail->cm_bins) {
                $rudBinCount += $detail->cm_bins->count();
            }
        }

        // It is a full credit for these lines if the RUD bin count perfectly matches the API bin count
        return ($apiBinCount > 0 && $apiBinCount === $rudBinCount);
    }

    /**
     * Helper to format the Sales Order Number dynamically.
     */
    private function getFormattedSoNumber()
    {
        return $this->rud->so_number !== null ? ltrim($this->rud->so_number, '0') : null;
    }

    /**
     * Builds the CI (Post Credit from Invoice) Data
     */
    private function buildSortCiDocData($soNumber, $apiData)
    {
        $lines = [];

        // Map the selected items to the API response to find the correct SalesOrderLine
        if ($this->rud && $this->rud->cm_details && !empty($apiData['details'])) {
            foreach ($this->rud->cm_details as $detail) {
                $stockCode = $detail->product->stock_code ?? null;
                if (!$stockCode) continue;

                $salesOrderLine = '';

                // Search the API Data for the matching StockCode
                foreach ($apiData['details'] as $apiLine) {
                    if (isset($apiLine['StockCode']) && $apiLine['StockCode'] === $stockCode) {
                        $salesOrderLine = $apiLine['SalesOrderLine'] ?? '';
                        break; // Stop searching once we find the match
                    }
                }

                $lines[] = [
                    'LineNumber'         => $salesOrderLine,
                    'DispatchNote'       => '',
                    'DispatchLineNumber' => '',
                ];
            }
        }

        // If there's only 1 line, flatten the array so the XML Converter Helper
        // doesn't wrap it in an <item> tag, preserving expected Syspro structure.
        if (count($lines) === 1) {
            $lines = $lines[0];
        }

        return [
            'CreditNoteNumber' => $soNumber,
            'InvoiceNumber'    => $this->rud->invoice_number ?? '',
            'Customer'         => $this->rud->account->account_code ?? '',
            'SalesOrder'       => $soNumber,
            'CreditReason'     => $this->rud->reason->reason_code ?? '',
            'POSSalesOrder'    => '',
            'Lines'            => empty($lines) ? [
                'LineNumber'         => '1', // Fallback if data is missing
                'DispatchNote'       => '',
                'DispatchLineNumber' => '',
            ] : $lines,
        ];
    }

    /**
     * Builds the CH (Credit Note Header) Data
     */
    private function buildSortChDocData($soNumber)
    {
        return [
            'Customer'             => $this->rud->account->account_code ?? '',
            'CreditNoteNumber'     => $soNumber,
            'CustomerPoNumber'     => $this->rud->po_number ?? '',
            'CreditNoteDate'       => $this->rud->cm_date ?? '',
            'InvoiceNumber'        => $this->rud->invoice_number ?? '',
            'ShipDate'             => $this->rud->ship_date ?? '',
            'ShipName'             => $this->rud->ship_name ?? '',
            'ShippingInstrs'       => $this->rud->shipping_instruction ?? '',
            'ShipAddress1'         => $this->rud->ship_address1 ?? '',
            'ShipAddress2'         => $this->rud->ship_address2 ?? '',
            'ShipAddress3'         => $this->rud->ship_address3 ?? '',
            'ShipAddress4'         => $this->rud->ship_address4 ?? '',
            'ShipAddress5'         => $this->rud->ship_address5 ?? '',
        ];
    }

    /**
     * Builds the CL (Credit Note Line/Bins) Data iteratively
     */
    private function buildSortClDocData($soNumber)
    {
        $lines = [];

        // Safety check to ensure relational data exists
        if (!$this->rud || !$this->rud->cm_details) {
            return $lines;
        }

        foreach ($this->rud->cm_details as $detail) {
            if (!$detail->cm_bins) continue;

            foreach ($detail->cm_bins as $bin) {
                $lines[] = [
                    'CreditNoteNumber'           => $soNumber,
                    'CreditReason'               => $this->rud->reason->reason_code ?? '',
                    'StockCode'                  => $detail->product->stock_code ?? '',
                    'Description'                => $detail->product->description ?? '',
                    'Warehouse'                  => $detail->warehouse ?? '',
                    'OrderQty'                   => $detail->order_quantity ?? 0,
                    'OrderUom'                   => $detail->order_uom ?? '',
                    'Price'                      => $detail->price ?? 0,
                    'PriceUom'                   => $detail->price_uom ?? '',
                    'Lot'                        => $bin->lot_number ?? '',
                    'Bins'                       => [
                        'BinLocation' => $bin->bin ?? '',
                        'BinQuantity' => $bin->quantity ?? 0,
                        'BinUnits'    => '',
                        'BinPieces'   => '',
                    ],
                    'DiscPercent1'               => '0',
                    'DiscPercent2'               => '0',
                    'DiscPercent3'               => '0',
                    'DiscValue'                  => '0',
                    'CustRequestDate'            => $this->rud->cm_date ?? '',
                    'UserDefined'                => 'DFM',
                ];
            }
        }

        return $lines;
    }

    /**
     * Fetches invoice details from the API.
     */
    private function getCmDetails() {
        $data = [];

        $account = $this->rud->account;
        $year = $this->rud->year;
        $month = $this->rud->month;
        $invoice_number = $this->rud->invoice_number;
        $so_number = $this->rud->so_number;
        $po_number = $this->rud->po_number;

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . env('API_TOKEN_SYSPRODATA'),
                'year' => $year,
                'month' => $month,
                'invoice_number' => $invoice_number,
                'company' => $account->company->name ?? null,
                'sales_order' => $so_number,
                'account_code' => $account->account_code ?? null,
                'po_number' => $po_number,
            ])->timeout(30)->get($this->api_base_url . 'getInvoiceData');

            if ($response->failed()) {
                if (method_exists($this, 'addError')) {
                    $this->addError('load_details', 'Failed to fetch invoice details from Syspro.');
                }
                return [];
            }
            $data = $response->json();

        } catch (\Exception $e) {
            if (method_exists($this, 'addError')) {
                $this->addError('load_details', 'Connection error: ' . $e->getMessage());
            }
        }

        return $data;
    }
}
