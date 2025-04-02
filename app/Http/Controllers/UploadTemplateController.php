<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UploadTemplate;

use App\Http\Traits\GlobalTrait;

class UploadTemplateController extends Controller
{
    use GlobalTrait;

    public $setting;

    public function __construct() {
        $this->setting = $this->getSettings();
    }

    public function index(Request $request) {

        $upload_templates = UploadTemplate::orderBy('created_at', 'DESC')
            ->paginate($this->setting->data_per_page)
            ->appends(request()->query());

        return view('upload-templates.index')->with([
            'upload_templates' => $upload_templates
        ]);
    }

    public function create() {
        return view('upload-templates.create');
    }

    public function store() {
        
    }

    public function show() {
    }

    public function edit() {
        
    }

    public function update() {

    }
}
