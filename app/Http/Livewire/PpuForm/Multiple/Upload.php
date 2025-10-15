<?php

namespace App\Http\Livewire\PpuForm\Multiple;

use Livewire\Component;

use Livewire\WithPagination;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
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
            if(!empty(trim($row[0]))) {
                if($key != 0) {
                    
                    $account = $this->account;

                    $rtv_number = trim($row[0]);
                    $date_submitted = $row[1];
                    $pickup_date = $row[2];
                    $rtv_date = $row[3];
                    $branch_name = trim($row[4]);
                    $total_quantity = (int)trim($row[5]);
                    $total_amount = (float)trim($row[6]);
                    $remarks = trim($row[7]);
    
    
                    if(is_int($date_submitted)) {
                        $date_submitted = Date::excelToDateTimeObject($date_submitted)->format('Y-m-d');
                    } else {
                        $dateTime = \DateTime::createFromFormat('m-d-Y', $date_submitted);
                        if ($dateTime === false) {
                            $date_submitted = $date_submitted;
                        } else {
                            $date_submitted = $dateTime->format('Y-m-d');
                        }
                    }

                    if(is_int($pickup_date)) {
                        $pickup_date = Date::excelToDateTimeObject($pickup_date)->format('Y-m-d');
                    } else {
                        $dateTime = \DateTime::createFromFormat('m-d-Y', $pickup_date);
                        if ($dateTime === false) {
                            $pickup_date = $pickup_date;
                        } else {
                            $pickup_date = $dateTime->format('Y-m-d');
                        }
                    }

                    if(is_int($rtv_date)) {
                        $rtv_date = Date::excelToDateTimeObject($rtv_date)->format('Y-m-d');
                    } else {
                        $dateTime = \DateTime::createFromFormat('m-d-Y', $rtv_date);
                        if ($dateTime === false) {
                            $rtv_date = $rtv_date;
                        } else {
                            $rtv_date = $dateTime->format('Y-m-d');
                        }
                    }

                    $data_arr['date_submitted'] = $date_submitted;
                    $data_arr['pickup_date'] = $pickup_date;
    
                    $data_arr['lines'][] = [
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

        $this->ppu_data = $data_arr;

        // dd($this->account);

    }

    private function generateControlNumber() {
        $date_code = date('Ymd');

        do {
            $control_number = 'PPU-'.$date_code.'-001';
            // get the most recent sales order
            $ppu_form = PPUForm::withTrashed()->orderBy('control_number', 'DESC')
                ->first();
            if(!empty($ppu_form)) {
                $latest_control_number = $ppu_form->control_number;
                list(, $prev_date, $last_number) = explode('-', $latest_control_number);

                // Increment the number based on the date
                $number = ($date_code == $prev_date) ? ((int)$last_number + 1) : 1;

                // Format the number with leading zeros
                $formatted_number = str_pad($number, 3, '0', STR_PAD_LEFT);

                // Construct the new control number
                $control_number = "PPU-$date_code-$formatted_number";
            }

        } while(PPUForm::withTrashed()->where('control_number', $control_number)->exists());

        return $control_number;
    }

    public function savePPUForm($status) {
        // validate
        $data = $this->ppu_data;

        $err = array();
        if(empty($data['lines'])) {
            $err['lines'] = 'Please add items first';
        }
        foreach($data['lines'] as $key => $item) {
            if(empty($item['rtv_number'])) {
                $err['rtv_number'] = 'RTV number is required';
            } else {
                // check for duplicates
                $check1 = PPUFormItem::where('rtv_number', $item['rtv_number'])->withTrashed()->exists();
                if(!empty($check1)) {
                    $err['rtv_number'] = 'RTV number '.$item['rtv_number'].' already exists';
                }
            }
        }

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
