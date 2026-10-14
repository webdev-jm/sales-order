<?php

namespace App\Http\Livewire\SalesOrderTemplate;

use App\Models\Account;
use App\Models\PoScanFormat;
use App\Services\PoScanner;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The "PO Formats" tab: one or more scan formats per account. A format's notes tell the AI how
 * that account lays out its POs, because every account prints them differently.
 */
class ScanFormats extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public ?int $editingId = null;
    public $account_id = '';
    public string $accountSearch = '';
    public string $name = '';
    public string $default_uom = 'CS';
    public string $instructions = '';
    public bool $is_active = true;

    protected $messages = [
        'account_id.required' => 'Please select an account.',
        'name.unique'         => 'This account already has a format with that name.',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'account_id'   => 'required|exists:accounts,id',
            'name'         => [
                'required', 'string', 'max:255',
                Rule::unique('po_scan_formats', 'name')
                    ->where('account_id', $this->account_id)
                    ->ignore($this->editingId),
            ],
            'default_uom'  => ['required', Rule::in(PoScanner::UOMS)],
            'instructions' => 'nullable|string|max:5000',
            'is_active'    => 'boolean',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $this->authorizeManage();
        $this->validate();

        $attributes = [
            'account_id'   => $this->account_id,
            'name'         => trim($this->name),
            'default_uom'  => $this->default_uom,
            'instructions' => trim($this->instructions),
            'is_active'    => $this->is_active,
        ];

        if ($this->editingId) {
            PoScanFormat::findOrFail($this->editingId)->update($attributes);
        } else {
            PoScanFormat::create($attributes + ['created_by' => auth()->id()]);
        }

        $this->cancel();
        session()->flash('scan_format_saved', 'PO format saved.');
    }

    public function edit(int $id): void
    {
        $this->authorizeManage();

        $format = PoScanFormat::findOrFail($id);

        $this->editingId    = $format->id;
        $this->account_id   = (string) $format->account_id;
        $this->name         = $format->name;
        $this->default_uom  = $format->default_uom;
        $this->instructions = (string) $format->instructions;
        $this->is_active    = $format->is_active;
        $this->resetValidation();
    }

    public function cancel(): void
    {
        $this->reset('editingId', 'account_id', 'accountSearch', 'name', 'default_uom', 'instructions', 'is_active');
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $this->authorizeManage();

        PoScanFormat::findOrFail($id)->delete();

        if ($this->editingId === $id) {
            $this->cancel();
        }
    }

    private function authorizeManage(): void
    {
        abort_unless(auth()->user()->can('po scan format manage'), 403);
    }

    public function render(): View
    {
        $accounts = Account::orderBy('account_name')
            ->when(trim($this->accountSearch) !== '', function ($q) {
                $search = '%' . trim($this->accountSearch) . '%';
                $q->where(function ($query) use ($search) {
                    $query->where('account_name', 'like', $search)
                        ->orWhere('account_code', 'like', $search);
                });
            })
            ->limit(50)
            ->get(['id', 'account_code', 'account_name']);

        if ($this->account_id !== '' && !$accounts->contains('id', (int) $this->account_id)) {
            $selected = Account::find($this->account_id, ['id', 'account_code', 'account_name']);
            $accounts = $selected ? $accounts->prepend($selected) : $accounts;
        }

        $formats = PoScanFormat::with('account:id,account_code,account_name')
            ->when(trim($this->search) !== '', function ($q) {
                $search = '%' . trim($this->search) . '%';
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhereHas('account', fn ($aq) => $aq
                            ->where('account_name', 'like', $search)
                            ->orWhere('account_code', 'like', $search));
                });
            })
            ->orderBy('account_id')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.sales-order-template.scan-formats', [
            'accounts' => $accounts,
            'formats'  => $formats,
            'uoms'     => PoScanner::UOMS,
        ]);
    }
}
