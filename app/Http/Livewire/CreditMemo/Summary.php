<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;
use App\Helpers\XmlConverterHelper;
use Illuminate\Support\Facades\Session;



class Summary extends Component
{
    public $summary_data;
    protected $listeners = [
        'setSummary' => 'setCmData'
    ];

    public function render()
    {
        return view('livewire.credit-memo.summary');
    }

    public function setCmData() {
        $this->summary_data = Session::get('cm_data');
    }

    public function generateXml() {
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
