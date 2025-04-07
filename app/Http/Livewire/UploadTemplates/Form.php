<?php

namespace App\Http\Livewire\UploadTemplates;

use Livewire\Component;

use Illuminate\Validation\Rule;
use App\Models\UploadTemplate;
use App\Models\UploadTemplateField;

class Form extends Component
{
    public $upload_template;
    public $type;
    public $lines;
    public $name, $breakpoint, $breakpoint_col;

    public function saveTemplate() {
        $this->validate([
            'name' => [
                'required',
                Rule::unique((new UploadTemplate)->getTable())
            ],
            'lines' => [
                'required'
            ],
            'breakpoint_col' => [
                function ($attribute, $value, $fail) {
                    if (!empty($this->breakpoint) && empty($value)) {
                        $fail('The breakpoint column field is required when breakpoint is set.');
                    }
                }
            ]
        ]);

        if(!empty($this->lines)) {

            if($this->type == 'add') {

                $template = new UploadTemplate([
                    'name' => $this->name,
                    'breakpoint' => $this->breakpoint ?? NULL,
                    'breakpoint_col' => $this->breakpoint_col ?? NULL,
                ]);
                $template->save();
            
                $num = 0;
                foreach($this->lines as $line) {
                    $num++;
    
                    $template_field = new UploadTemplateField([
                        'upload_template_id' => $template->id,
                        'number' => $num,
                        'column_name' => $line['column_name'],
                        'column_number' => $line['column_number'],
                    ]);
                    $template_field->save();
                }
            } else if($this->type == 'edit') {
                $this->upload_template->update([
                    'name' => $this->name,
                    'breakpoint' => $this->breakpoint ?? NULL,
                    'breakpoint_col' => $this->breakpoint_col ?? NULL,
                ]);

                $this->upload_template->template_fields->delete();
                $num = 0;
                foreach($this->lines as $line) {
                    $num++;
    
                    $template_field = new UploadTemplateField([
                        'upload_template_id' => $template->id,
                        'number' => $num,
                        'column_name' => $line['column_name'],
                        'column_number' => $line['column_number'],
                    ]);
                    $template_field->save();
                }
            }

        }

        return redirect()->route('upload-template.index')->with([
            'message_success' => 'Upload template has been created successfully.'
        ]);
    }

    public function addLine() {
        $this->lines[] = [
            'column_name' => '',
            'column_number' => ''
        ];
    }

    public function removeLine($key) {
        unset($this->lines[$key]);
    }

    public function mount($type, $upload_template) {
        $this->type = $type;
        $this->upload_template = $upload_template;

        if($this->type == 'add') {
            $this->lines[] = [
                'column_name' => '',
                'column_number' => ''
            ];
        } else if($this->type == 'edit') {
            $this->name = $upload_template->name;
            $this->breakpoint = $upload_template->breakpoint;
            $this->breakpoint_col = $upload_template->breakpoint_col;

            foreach($upload_template->template_fields as $field) {
                $this->lines[] = [
                    'id' => $field->id,
                    'column_name' => $field->column_name,
                    'column_number' => $field->column_number
                ];
            }
        }
    }
    
    public function render()
    {
        return view('livewire.upload-templates.form');
    }
}
