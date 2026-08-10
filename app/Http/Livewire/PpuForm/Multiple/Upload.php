<?php

namespace App\Http\Livewire\PpuForm\Multiple;

use Livewire\Component;

use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\PPUForm;
use App\Models\SalesOrderProduct;
use App\Models\PPUFormItem;
use App\Models\SalesOrderProductUom;
use App\Models\PurchaseOrderNumber;
use App\Models\ShippingAddress;

use App\Http\Traits\SoProductPriceTrait;
use App\Http\Traits\GlobalTrait;

class Upload extends Component
{
    use WithFileUploads;
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    use SoProductPriceTrait;
    use GlobalTrait;

    public $logged_account;
    public $account;
    public $shipping_addresses;
    public $setting;
    public $so_file;
    public $ppu_data;
    public $err_data;
    public $success_data;

    public function checkFileData() {
        $this->validate([
            'so_file' => 'required|mimetypes:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel'
        ]);

        $path1 = $this->so_file->storeAs('multiple-so', $this->so_file->getClientOriginalName());
        $path = storage_path('app').'/'.$path1;
        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $rowResults = []; // Array to store results for this row
            foreach ($row->getCellIterator() as $cell) {
                $rowResults[] = $cell->getCalculatedValue(); // Store the result of the formula
            }
            $data[] = $rowResults; // Store the results for this row in the main results array
        }

        $this->processData($data);
    }

    private function processData($data) {
        $this->reset([
            'ppu_data',
            'err_data',
            'success_data'
        ]);

        $data_arr = array();
        foreach($data as $key => $row) {
            if(!empty(trim($row[0] ?? ''))) {
                if($key != 0) {
                    
                    $account = $this->account;

                    $rtv_number = trim($row[0]);
                    $date_submitted = $this->parseSpreadsheetDate($row[1] ?? null);
                    $pickup_date = $this->parseSpreadsheetDate($row[2] ?? null);
                    $rtv_date = $this->parseSpreadsheetDate($row[3] ?? null);
                    $branch_name = trim($row[4] ?? '');
                    $total_quantity = (int)trim($row[5] ?? '');
                    $total_amount = (float)trim($row[6] ?? '');
                    $remarks = trim($row[7] ?? '');

                    /** The header dates repeat on every line; keep the first readable one. */
                    if($date_submitted !== null && empty($data_arr['date_submitted'])) {
                        $data_arr['date_submitted'] = $date_submitted;
                    }
                    if($pickup_date !== null && empty($data_arr['pickup_date'])) {
                        $data_arr['pickup_date'] = $pickup_date;
                    }
    
                    $data_arr['lines'][] = [
                        'row_number' => $key + 1,
                        'rtv_number' => $rtv_number,
                        'rtv_date' => $rtv_date,
                        'branch_name' => $branch_name,
                        'total_quantity' => $total_quantity,
                        'total_amount' => $total_amount,
                        'remarks' => $remarks,
                    ];
                }
            }
        }

        if(!empty($data_arr['lines'])) {
            $data_arr['date_submitted'] = $data_arr['date_submitted'] ?? null;
            $data_arr['pickup_date'] = $data_arr['pickup_date'] ?? null;
        }

        $this->ppu_data = $data_arr;

        $this->err_data = $this->validateUpload($data_arr);
    }

    private function generateControlNumber(): string
    {
        $year = date('Y');

        return DB::transaction(function () use ($year) {
            $latest = PPUForm::withTrashed()
                ->where('control_number', 'like', "PPU-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('control_number')
                ->value('control_number');

            $next = $latest ? ((int) substr($latest, strrpos($latest, '-') + 1)) + 1 : 1;

            return \sprintf('PPU-%s-%03d', $year, $next);
        });
    }

    /**
     * Validate the whole upload: the header dates plus every line.
     *
     * A date that the spreadsheet parser could not resolve arrives here as
     * null, so an unreadable cell is reported instead of silently saving an
     * empty date.
     *
     * @param  array{date_submitted?: ?string, pickup_date?: ?string, lines?: array}  $data
     */
    private function validateUpload(array $data): array
    {
        $err = $this->validateLines($data['lines'] ?? []);

        if (empty($data['date_submitted'])) {
            $err['date_submitted'] = 'Submitted date is missing or its format could not be read';
        }

        if (empty($data['pickup_date'])) {
            $err['pickup_date'] = 'Pick-up date is missing or its format could not be read';
        }

        return $err;
    }

    private function validateLines(array $lines): array
    {
        $err = [];

        if (empty($lines)) {
            $err['lines'] = 'Please add items first';
            return $err;
        }

        $rtvCounts = [];
        foreach ($lines as $item) {
            $normalized = mb_strtolower(trim($item['rtv_number'] ?? ''));
            if ($normalized !== '') {
                $rtvCounts[$normalized] = ($rtvCounts[$normalized] ?? 0) + 1;
            }
        }

        foreach ($lines as $key => $item) {
            $rowErr = [];
            $rtvNumber = trim($item['rtv_number'] ?? '');

            if ($rtvNumber === '') {
                $rowErr['rtv_number'] = 'RTV number is required';
            } else {
                $normalized = mb_strtolower($rtvNumber);
                if (($rtvCounts[$normalized] ?? 0) > 1) {
                    $rowErr['rtv_number'] = 'RTV number '.$rtvNumber.' is duplicated within this upload';
                } elseif (PPUFormItem::where('rtv_number', $rtvNumber)->withTrashed()->exists()) {
                    $rowErr['rtv_number'] = 'RTV number '.$rtvNumber.' already exists';
                }
            }

            if (empty($item['rtv_date'])) {
                $rowErr['rtv_date'] = 'RTV date is missing or its format could not be read';
            }

            if (!empty($rowErr)) {
                $err['rows'][$key] = $rowErr;
            }
        }

        return $err;
    }

    public function recheckLines()
    {
        $this->success_data = null;
        $this->err_data = $this->validateUpload($this->ppu_data ?? []);
    }

    public function savePPUForm($status) {
        // validate
        $data = $this->ppu_data;

        $err = $this->validateUpload($data ?? []);

        if(empty($err)) {
            // create sales order
            $control_number = $this->generateControlNumber();

            $ppu_form = new PPUForm([
                'account_login_id' => $this->logged_account->id,
                'control_number' => $control_number,
                'date_prepared' => date('Y-m-d'),
                'pickup_date' => $data['pickup_date'],
                'date_submitted' => $data['date_submitted'],
                'status' => $status,
            ]);
            $ppu_form->save();

            $num = 0;

            $total_quantity = 0;
            $total_amount = 0;

            foreach($data['lines'] as $key => $item) {

                $ppu_form_item = new PPUFormItem([
                    'ppuform_id' => $ppu_form->id,
                    'rtv_number' => $item['rtv_number'],
                    'rtv_date' => $item['rtv_date'],
                    'branch_name' => $item['branch_name'],
                    'total_quantity' => $item['total_quantity'],
                    'total_amount' => $item['total_amount'],
                    'remarks' => $item['remarks'],
                ]);
                $ppu_form_item->save();


                $total_quantity += $item['total_quantity'];
                $total_amount += $item['total_amount'];
            }


            $ppu_form->update([
                'total_quantity' => $total_quantity,
                'total_amount' => $total_amount
            ]);

            // logs
            activity('create')
                ->performedOn($ppu_form)
                ->log(':causer.firstname :causer.lastname has created ppu form :subject.control_number');

            $this->success_data = [
                'message' => 'PPU Form '.$control_number.' has been created.',
                'control_number' => $control_number,
                'status' => $status
            ];
        } else {
            $this->err_data = $err;
        }
    }

    public function saveAll($status) {
        $this->reset([
            'success_data',
            'err_data',
        ]);
        $this->savePPUForm($status);
    }

    public function mount($logged_account) {
        $this->logged_account = $logged_account;
        $this->account = $logged_account->account;
        $shipping_addresses = ShippingAddress::where('account_id', $this->account->id)
            ->orderBy('address_code', 'ASC')
            ->get();

        $this->shipping_addresses = $shipping_addresses->map(function ($address) {
            return array_map('trim', $address->toArray());
        });

        $this->setting = $this->getSettings();
    }

    public function render()
    {
        return view('livewire.ppu-form.multiple.upload');
    }
}
