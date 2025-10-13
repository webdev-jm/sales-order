<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\Account;
use App\Models\CreditMemoReason;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

use App\Helpers\XmlConverterHelper;

class Create extends Component
{

    public $accounts;
    public $reasons;
    public $year, $month, $invoice_number, $account_id, $so_number, $po_number;
    public $cm_reason_id;
    public $invoice_data;
    public $detail_data;

    public $api_url = "192.168.11.240/refreshable/public/api/credit-memo/";

    public function render()
    {
        return view('livewire.credit-memo.create');
    }

    public function mount() {
        $this->accounts = Account::orderBy('account_code', 'ASC')->get();
        $this->reasons = CreditMemoReason::orderBy('reason_code', 'DESC')->get();

        $this->year = date('Y');
        $this->month = (int)date('m');

        $this->cm_data = Session::get('cm_data');
        if(empty($this->cm_data)) {
            $this->cm_data = [
                'account_id' => $this->account_id,
                'cm_reason_id' => $this->cm_reason_id,
                'invoice_number' => $this->invoice_number,
                'so_number' => $this->so_number,
                'po_number' => '',
                'warehouse_location' => '',
                'ship_date' => '',
            ];

            Session::put('cm_data', $this->cm_data);
        }
    }

    public function searchInvoice() {
        $account = Account::find($this->account_id);
        $company = $account ? $account->company->name : null;

        $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'year' => $this->year,
                'month' => $this->month,
                'invoice_number' => $this->invoice_number,
                'company' => $company,
                'so_number' => $this->so_number,
                'po_number' => $this->po_number,
                'account_code' => $account->account_code ?? '',
            ])
            ->get($this->api_url.'getInvoice');

        $this->invoice_data = $response->json();

        $this->reset('detail_data');
    }

    public function selectSalesOrder($key) {
        $invoice = $this->invoice_data[$key];
        $account = Account::where('account_code', $invoice['Customer'])->first();
        $company = $account ? $account->company->name : null;

        $response = Http::withHeaders([
            'Accept' => 'application/json',
                'Authorization' => 'Bearer '.env('API_TOKEN_SYSPRODATA'),
                'year' => $invoice['TrnYear'],
                'month' => $invoice['TrnMonth'],
                'invoice_number' => $invoice['InvoiceNumber'],
                'company' => $company,
                'sales_order' => $invoice['SalesOrder'],
                'account_code' => $invoice['Customer'] ?? '',
        ])
        ->get($this->api_url.'getInvoiceDetail');

        $this->detail_data = $response->json();

        $this->so_number = $invoice['SalesOrder'];
        $this->invoice_number = $invoice['InvoiceNumber'];
        $this->po_number = $invoice['CustomerPoNumber'];

        $this->updateSession();
    }

    public function clearDetail() {
        $this->reset('detail_data');
        $this->reset(['so_number', 'invoice_number']);
    }

    public function updateSession() {
        $this->cm_data = [
            'account_id' => $this->account_id,
            'cm_reason_id' => $this->cm_reason_id,
            'invoice_number' => $this->invoice_number,
            'so_number' => $this->so_number,
            'po_number' => $this->po_number,
            'warehouse_location' => '',
            'ship_date' => '',
            'detail_data' => $this->detail_data,
        ];

        Session::put('cm_data', $this->cm_data);
    }

    public function saveRUD() {
        $this->validate([
            'cm_data.account_id' => [
                'required',
            ],
            'cm_data.invoice_number' => [
                'required',
            ],
            'cm_data.so_number' => [
                'required',
            ],
            'cm_reason_id' => [
                'required',
            ]
        ]);

        $xml = new XmlConverterHelper();

        //CI
        $post_credit_from_invoice = [
            [
                'CreditNoteNumber' => '',
                'InvoiceNumber' => '842',
                'Customer' => '',
                'SalesOrder' => '',
                'Lines' => [
                    'LineNumber' => 1,
                    'DispatchNote' => '',
                    'DispatchLineNumber' => '',
                ],
            ]
        ];

        // header
        $so_credit_note_header = [
            [
                'Customer' => 1,
                'CreditNoteNumber' => '',
                'CustomerPoNumber' => 'CP01',
                'CreditNoteDate' => '2007-03-04',
                'InvoiceNumber' => '',
                'Branch' => '',
                'Salesperson' => '',
                'ARInvoiceTerms' => '',
                'OrderType' => '',
                'PaymentMethod' => '',
                'GeographicArea' => '',
                'AlternateReference' => '',
                'MultipleShipCode' => '',
                'ShipDate' => '2007-03-04',
                'ShipName' => 'Bycicle Depot',
                'ShippingInstrs' => 'Road transport',
                'ShippingInstrsCode' => 'R',
                'ShipAddress1' => 'This is the alternate delivery address 1',
                'ShipAddress2' => 'This is the alternate delivery address 2',
                'ShipAddress3' => 'This is the alternate delivery address 3',
                'ShipAddress3Locality' => 'This is the alternate delivery address 3 locality',
                'ShipAddress4' => 'This is the alternate delivery address 4',
                'ShipAddress5' => 'This is the alternate delivery address 5',
                'ShipPostalCode' => '90210',
                'ShipGPSLat' => '12.123456',
                'ShipGPSLong' => '123.123456',
                'LanguageCode' => '',
                'Email' => 'Sender001@Sender001.com',
                'SpecialInstrs' => 'Handle with care',
                'OrderDiscPercent1' => '2.50',
                'OrderDiscPercent2' => '1.50',
                'OrderDiscPercent3' => '1.00',
                'Nationality' => '',
                'DeliveryTerms' => '',
                'ShippingLocation' => '',
                'TransactionNature' => '',
                'TransportMode' => '',
                'ProcessFlag' => '',
                'TaxExemptNumber' => '',
                'TaxExemptStatus' => '',
                'GstExemptNumber' => '',
                'GstExemptStatus' => '',
                'CompanyTaxNumber' => '',
                'State' => '',
                'CountyZip' => '',
                'City' => '',
                'OrderComments' => '',
                'DocumentFormat' => '',
                'eSignature' => '',
                'POSSalesOrder' => '',
                'PriceGroup' => '',
                'PriceGroupLevel' => '',
            ]
        ];

        // detail
        $so_credit_note_line = [
            [
                'CreditNoteNumber' => '800150',
                'CreditReason' => '',
                'NonStockedLine' => '',
                'StockCode' => 'B100',
                'Description' => 'Bicycle',
                'Revision' => '',
                'Release' => '',
                'Warehouse' => 'FG',
                'CustomerPartNumber' => 'FF334221',
                'OrderQty' => 5,
                'OrderUom' => 'EA',
                'AllowZeroQty' => 'N',
                'AllowZeroPrice' => 'N',
                'Price' => 400,
                'PriceUom' => 'EA',
                'PriceCode' => '',
                'PriceGroupRule' => '',
                'OrderUnits' => '',
                'OrderPieces' => '',
                'Serials' => [
                    'SerialNumber' => '',
                    'SerialQuantity' => '',
                    'SerialUnits' => '',
                    'SerialPieces' => '',
                    'SerialCreationDate' => '',
                    'SerialExpiryDate' => '',
                    'SerialScrapDate' => '',
                    'SerialLocation' => '',
                    'SerialBin' => '',
                ],
                'Lot' => 500,
                'Bins' => [
                    'BinLocation' => 'FG',
                    'BinQuantity' => 5.000,
                    'BinUnits' => '',
                    'BinPieces' => '',
                ],
                'ProductClass' => '',
                'DiscPercent1' => 0.5,
                'DiscPercent2' => 0,
                'DiscPercent3' => 0,
                'DiscValue' => 0,
                'DiscValFlag' => '',
                'OverrideCalculatedDiscount' => '',
                'CustRequestDate' => '2006-12-20',
                'CommissionCode' => '',
                'ShipDate' => '',
                'UserDefined' => 'USER',
                'UnitMass' => '',
                'UnitVolume' => '',
                'StockTaxCode' => '',
                'StockNotTaxable' => '',
                'StockFstCode' => '',
                'StockNotFstTaxable' => '',
                'NsProductClass' => 'NSPR',
                'NsUnitCost' => '',
                'Intrastat' => [
                    'IntrastatExempt' => 'Y',
                    'TriangulationRole' => '',
                    'DispatchState' => '',
                    'DestinationState' => '',
                    'CountryOfOrigin' => '',
                ],
                'eSignature' => '',
            ]
        ];

    }
}
