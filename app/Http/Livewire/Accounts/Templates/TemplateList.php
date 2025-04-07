<?php

namespace App\Http\Livewire\Accounts\Templates;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;

use App\Models\AccountUploadTemplate;
use App\Models\AccountUploadTemplateField;
use App\Models\UploadTemplate;

class TemplateList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $account;
    public $templates;
    public $template_fields;
    public $addTemplate = false;

    public $account_template;

    public $upload_template_id, $type, $start_row;
    public $account_template_fields = [];

    public function mount($account) {
        $this->account = $account;
        $this->templates = UploadTemplate::get();
    }

    public function render() {
        $account_templates = AccountUploadTemplate::where('account_id', $this->account->id)
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'account-template-page');

        return view('livewire.accounts.templates.template-list', compact('account_templates'));
    }

    public function editTemplate($template_id) {
        $this->account_template = AccountUploadTemplate::findOrFail($template_id);
        $this->upload_template_id = $this->account_template->upload_template_id;
        $this->type = $this->account_template->type;
        $this->start_row = $this->account_template->start_row;

        $this->updatedUploadTemplateId();

        $this->account_template_fields = $this->account_template->account_template_fields
            ->keyBy('upload_template_field_id')
            ->map(fn($field) => [
                'name' => $field->column_name,
                'number' => $field->column_number,
            ])
            ->toArray();

        $this->addTemplate = true;
    }

    public function updatedUploadTemplateId() {
        $template = UploadTemplate::find($this->upload_template_id);
        $this->account_template_fields = [];
        $this->template_fields = $template?->template_fields ?? [];
    }

    public function toggleTemplateForm() {
        $this->addTemplate = !$this->addTemplate;

        $this->resetTemplateForm();
    }

    public function saveTemplate() {
        $uploadTemplateRule = Rule::unique((new AccountUploadTemplate)->getTable())
            ->where('account_id', $this->account->id);

        if ($this->account_template) {
            $uploadTemplateRule->ignore($this->account_template->id);
        }

        $this->validate([
            'upload_template_id' => ['required', $uploadTemplateRule],
            'type' => ['required'],
            'start_row' => ['required'],
            'account_template_fields' => ['required'],
        ]);

        $template = $this->account_template ?? new AccountUploadTemplate([
            'account_id' => $this->account->id,
        ]);

        $template->fill([
            'upload_template_id' => $this->upload_template_id,
            'type' => $this->type,
            'start_row' => $this->start_row,
        ])->save();

        // Handle field updates
        $this->syncTemplateFields($template);

        $this->resetTemplateForm();
        $this->reset('addTemplate');
    }

    private function syncTemplateFields(AccountUploadTemplate $template) {
        $template->account_template_fields()->delete(); // Just delete and recreate for simplicity

        $number = 1;
        foreach ($this->account_template_fields as $upload_field_id => $val) {
            $template->account_template_fields()->create([
                'upload_template_field_id' => $upload_field_id,
                'number' => $number++,
                'column_name' => $val['name'],
                'column_number' => $val['number'],
            ]);
        }
    }

    private function resetTemplateForm() {
        $this->reset([
            'upload_template_id',
            'type',
            'start_row',
            'account_template_fields',
            'template_fields',
            'account_template',
        ]);

    }
}
