<?php

namespace App\Http\Livewire\Remittances;

use Livewire\Component;
use Livewire\WithFileUploads;

use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\SimpleExcel\SimpleExcelReader;

use App\Models\AccountUploadTemplate;
use App\Models\UploadTemplate;

class Upload extends Component
{
    use WithFileUploads;

    public $template_name = 'REMITTANCE';
    public $upload_template;
    public $account_template;
    public $account_template_fields;
    public $upload_file = [];

    public $remittance_data = [];

    public function render()
    {
        return view('livewire.remittances.upload');
    }

    public function mount() {
        $this->upload_template = UploadTemplate::where('name', $this->template_name)
            ->first();

        $this->account_template = AccountUploadTemplate::where('account_id', 285)
            ->where('upload_template_id', $this->upload_template->id)
            ->first();

            $this->account_template_fields = array();
            if(!empty($this->account_template)) {
                $this->account_template_fields = $this->account_template->account_template_fields->mapWithKeys(function($field) {
                    return [
                        $field->upload_template_field_id => [
                            'number' => $field->number,
                            'column_name' => $field->column_name,
                            'column_number' => $field->column_number,
                        ]
                    ];
                });
            }
    }

    public function checkFile() {
        
        $this->validate([
            'upload_file.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $allowed = ['xls', 'xlsx', 'csv', 'xlsb'];
                    if (!in_array($value->getClientOriginalExtension(), $allowed)) {
                        $fail('One or more uploaded files are not valid Excel or CSV formats.');
                    }
                }
            ],
        ]);

        $remittance_data = [];
        foreach($this->upload_file as $file) {
            $filename = time().'_'.preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $path1 = $file->storeAs('remittances', $filename);
            $path = storage_path('app').'/'.$path1;

            $extension = $file->getClientOriginalExtension();
            if(in_array($extension, ['xlsx', 'csv', 'bin', 'xls'])) {
                // convert xls to xlsx
                $spreadsheet = IOFactory::load($path);
                $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
                $xlsxPath = storage_path('app').'/converted-file-'.time().'.xlsx';
                $writer->save($xlsxPath);

                $rows = [];
                if($this->account_template->type == 'name') {
                    $rows = SimpleExcelReader::create($xlsxPath)
                        ->getRows();
                } elseif($this->account_template->type == 'number') {
                    $rows = SimpleExcelReader::create($xlsxPath)
                        ->skip($this->account_template->start_row - 2)
                        ->noHeaderRow()
                        ->getRows();
                }

                
                if(!empty($rows)) {
                    $row_num = 0;
                    $control_number = 0;
                    $rows->each(function($row, $key) use(&$remittance_data, &$header_val, &$detail_val, &$row_num, &$control_number) {
                        
                        if(!empty($this->account_template->breakpoint) && $row[$this->account_template->breakpoint_col - 1] == $this->account_template->breakpoint) {
                            $row_num = 0;
                            $control_number++;
                        } else {
                            $row_num++;
                        }

                        $payment_reference = '';
                        if($row_num >= 1 && $row_num <= 5) {
                            $header_row = [];
                            foreach($row as $val) {
                                if(!empty($val)) {
                                    $header_row[] = $val;
                                }
                            }
                            $header_val[$control_number][] = $header_row;
                        }

                        // $detail_val = [];
                        if($row_num >= 6) {
                            $detail_val[$control_number][] = $row;
                        }
                    });

                    $data = [
                        'row' => $row_num,
                        'headers' => $header_val,
                        'details' => $detail_val
                    ];

                    $this->processData($data);
                }
            } elseif($extension == 'xml') {

            }

            
        }
        
    }

    public function processData($remittance_data) {
        $headers = $remittance_data['headers'];
        $details = $remittance_data['details'];

        foreach($headers as $key => $header) {
            $assoc = [];
            foreach($header as $index => $val) {
                if($index <= 2) {
                    for($i = 0; $i < count($val); $i += 2) {
                        $index_val = trim(str_replace(':', '', $val[$i]));
                        $value = isset($val[$i + 1]) ? trim($val[$i + 1]) : NULL;
                        $assoc[$index_val] = $value;
                    }
                }
            }
 
            $this->remittance_data[$key]['header'] = $assoc;
        }

        foreach($details as $key => $detail_arr) {
            $detail_data = [];
            foreach($detail_arr as $index => $detail) {
                foreach($this->upload_template->template_fields as $template_field) {
                    $column_name = $template_field->column_name;

                    if($this->account_template->type == 'number') {
                        $value = $detail[$this->account_template_fields[$template_field->id]['column_number']];
                        $detail_data[$index][$column_name] = $value;
                    } elseif($this->account_template->type == 'name') {
                        $value = $detail[$this->account_template_fields[$template_field->id]['column_name']];
                        $detail_data[$index][$column_name] = $value;
                    }

                }

            }

            $this->remittance_data[$key]['details'] = $detail_data;
        }
    }

}
