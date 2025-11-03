<?php

namespace App\Http\Livewire\CreditMemo;

use Livewire\Component;

use App\Models\CreditMemoApproval;
use App\Helpers\XmlConverterHelper;

class Approvals extends Component
{
    public $rud;

    public $status_arr = [
        'draft' => 'secondary',
        'submitted' => 'info',
        'returned' => 'danger',
        'approved' => 'success',
    ];

    public function render()
    {
        return view('livewire.credit-memo.approvals');
    }

    public function mount($credit_memo) {
        $this->rud = $credit_memo;

        $this->generateXML();
    }

    public function approve($status) {
        $changes_arr['old'] = $this->rud->getOriginal();

        $this->rud->update([
            'status' => $status,
        ]);

        $changes_arr['old'] = $this->rud->getChanges();

        $approval = new CreditMemoApproval([
            'credit_memo_id' => $this->rud->id,
            'user_id' => auth()->user()->id,
            'status' => $status,
            'remarks' => NULL
        ]);
        $approval->save();

        $this->emit('updateHistory');

        // logs
        activity('updated')
            ->performedOn($this->rud)
            ->withProperties($changes_arr)
            ->log(':causer.firstname :causer.lastname has '.$status.' a RUD Invoice: :subject.invoice_number');

    }

    public function generateXML() {
        $so_number = $this->rud->so_number !== null ? ltrim($this->rud->so_number, '0') : null;

        $sortcidoc_xsd = [
            [
                'CreditNoteNumber' => $so_number,
                'InvoiceNumber' => $this->rud->invoice_number,
                'Customer' => $this->rud->account->account_code,
                'SalesOrder' => $so_number,
                'CreditReason' => $this->rud->reason->reason_code,
                'POSSalesOrder' => '',
                'Lines' => [
                    'LineNumber' => '1',
                    'DispatchNote' => '',
                    'DispatchLineNumber' => '',
                ],
            ],
        ];

        $sortchdoc_xsd = [
            [
                'Customer' => $this->rud->account->account_code,
                'CreditNoteNumber' => $so_number,
                'CustomerPoNumber' => $this->rud->po_number,
                'CreditNoteDate' => $this->rud->cm_date,
                'InvoiceNumber' => $this->rud->invoice_number,
                'Branch' => '',
                'Salesperson' => '',
                'ARInvoiceTerms' => '',
                'OrderType' => '',
                'PaymentMethod' => '',
                'GeographicArea' => '',
                'AlternateReference' => '',
                'MultipleShipCode' => '',
                'ShipDate' => $this->rud->ship_dates,
                'ShipName' => $this->rud->ship_name,
                'ShippingInstrs' => $this->rud->shipping_instruction,
                'ShippingInstrsCode' => '',
                'ShipAddress1' => $this->rud->ship_address1,
                'ShipAddress2' => $this->rud->ship_address2,
                'ShipAddress3' => $this->rud->ship_address3,
                'ShipAddress3Locality' => '',
                'ShipAddress4' => $this->rud->ship_address4,
                'ShipAddress5' => $this->rud->ship_address5,
                'ShipPostalCode' => '',
                'ShipGPSLat' => '',
                'ShipGPSLong' => '',
                'LanguageCode' => '',
                'Email' => '',
                'SpecialInstrs' => '',
                'OrderDiscPercent1' => '',
                'OrderDiscPercent2' => '',
                'OrderDiscPercent3' => '',
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
            ],
        ];

        $sortcldoc_xsd = [];
        foreach($this->rud->cm_details as $detail) {
            foreach($detail->cm_bins as $bin) {
                $sortcldoc_xsd[] = [
                    'CreditNoteNumber' => $so_number,
                    'CreditReason' => $this->rud->reason->reason_code,
                    'NonStockedLine' => '',
                    'StockCode' => $detail->product->stock_code,
                    'Description' => $detail->product->description,
                    'Warehouse' => $detail->warehouse,
                    'CustomerPartNumber' => '',
                    'OrderQty' => $detail->order_quantity,
                    'OrderUom' => $detail->order_uom,
                    'Price' => $detail->price,
                    'PriceUom' => $detail->price_uom,
                    'PriceCode' => '',
                    'PriceGroupRule' => '',
                    'OrderUnits' => '',
                    'OrderPieces' => '',
                    'Lot' => $bin->lot_number,
                    'Bins' => [
                        'BinLocation' => $bin->bin,
                        'BinQuantity' => $bin->quantity,
                        'BinUnits' => '',
                        'BinPieces' => '',
                    ],
                    'ProductClass' => '',
                    'DiscPercent1' => '0',
                    'DiscPercent2' => '0',
                    'DiscPercent3' => '0',
                    'DiscValue' => '0',
                    'DiscValFlag' => '',
                    'OverrideCalculatedDiscount' => '',
                    'CustRequestDate' => $this->rud->cm_date,
                    'CommissionCode' => '',
                    'ShipDate' => '',
                    'UserDefined' => 'DFM',
                    'UnitMass' => '',
                    'UnitVolume' => '',
                    'StockTaxCode' => '',
                    'StockNotTaxable' => '',
                    'StockFstCode' => '',
                    'StockNotFstTaxable' => '',
                    'NsProductClass' => '',
                    'NsUnitCost' => '',
                    'eSignature' => '',
                ];
            }
        }

        $xmlConverter = new XmlConverterHelper();
        $sortci_xml = $xmlConverter->arrayToXml($sortcidoc_xsd, 'PostCreditFromInvoice', 'SORTCIDOC.XSD');
        $sortch_xml = $xmlConverter->arrayToXml($sortchdoc_xsd, 'SOCreditNoteHeader', 'SORTCHDOC.XSD');
        $sortcl_xml = $xmlConverter->arrayToXml($sortcldoc_xsd, 'SOCreditNoteLine', 'SORTCLDOC.XSD');


    }
}
