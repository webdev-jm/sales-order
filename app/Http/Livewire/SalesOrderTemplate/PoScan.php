<?php

namespace App\Http\Livewire\SalesOrderTemplate;

use App\Exceptions\AiUnavailableException;
use App\Models\Account;
use App\Models\PoScanFormat;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiUsageMeter;
use App\Services\PoScanner;
use App\Services\SoTemplateLookup;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * The "Scan PDF" tab: reads an account's PO PDFs with AI and hands the rows to the template
 * review screen, which then exports them like an uploaded CSV.
 */
class PoScan extends Component
{
    use WithFileUploads;

    public $files = [];
    public $account_id = '';
    public $format_id = '';
    public $accountSearch = '';
    public bool $readAgain = false;

    /** @var array<string, string> PDF file name => why it could not be scanned */
    public array $scanErrors = [];

    protected $messages = [
        'account_id.required' => 'Please select the account the PO is from.',
        'files.required'      => 'Please choose at least one PDF.',
        'files.max'           => 'Scan at most :max PDFs at a time.',
        'files.*.mimes'       => 'Only PDF files can be scanned.',
        'files.*.max'         => 'Each PDF must be smaller than :max KB.',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'format_id'  => 'nullable|exists:po_scan_formats,id',
            'files'      => 'required|array|min:1|max:' . config('ai.po_scan.max_files'),
            'files.*'    => 'file|mimes:pdf|max:' . config('ai.po_scan.max_file_kb'),
        ];
    }

    public function updatedAccountId(): void
    {
        $this->format_id = (string) (PoScanFormat::active()
            ->where('account_id', $this->account_id)
            ->orderBy('name')
            ->value('id') ?? '');
    }

    public function scan(): void
    {
        abort_unless(auth()->user()->can('po scan access'), 403);

        $this->scanErrors = [];
        $this->validate();

        if (!$this->canUseAccount((int) $this->account_id)) {
            $this->addError('account_id', 'You do not have access to this account.');
            return;
        }

        $account = Account::findOrFail($this->account_id);
        $format  = $this->format_id !== ''
            ? PoScanFormat::active()->where('account_id', $account->id)->find($this->format_id)
            : null;

        $scanner = app(PoScanner::class);
        $rows    = [];

        set_time_limit(30 + count($this->files) * app(AiClient::class)->maxDurationSeconds());

        foreach ($this->files as $file) {
            $fileName = $file->getClientOriginalName();

            try {
                array_push($rows, ...$scanner->scan($file->get(), $fileName, $account, $format, auth()->user(), ! $this->readAgain));
            } catch (AiUnavailableException $e) {
                $this->scanErrors[$fileName] = $e->getMessage();
            }
        }

        if ($rows === []) {
            return;
        }

        Session::put('so_template_rows', $rows);
        $this->emit('poScanned', SoTemplateLookup::summarize($rows), $this->scanErrors);
        $this->reset('files', 'scanErrors', 'readAgain');
    }

    private function canUseAccount(int $accountId): bool
    {
        if (auth()->user()->hasRole('superadmin')) {
            return true;
        }

        return auth()->user()->accounts()->where('accounts.id', $accountId)->exists();
    }

    public function render(): View
    {
        $accounts = Account::orderBy('account_name')
            ->when(!auth()->user()->hasRole('superadmin'), function ($q) {
                $q->whereHas('users', function ($query) {
                    $query->where('users.id', auth()->id());
                });
            })
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
            $accounts = $selected && $this->canUseAccount($selected->id) ? $accounts->prepend($selected) : $accounts;
        }

        $formats = $this->account_id !== ''
            ? PoScanFormat::active()->where('account_id', $this->account_id)->orderBy('name')->get()
            : collect();

        $meter = app(AiUsageMeter::class);
        $ai    = app(AiClient::class);

        return view('livewire.sales-order-template.po-scan', [
            'accounts'      => $accounts,
            'formats'       => $formats,
            'aiProblem'     => $ai->setupProblem(),
            'aiProviders'   => $ai->setupProblem() === null ? $ai->chain() : [],
            'tokensUsed'    => $meter->usedInMonth(),
            'tokenLimit'    => $meter->monthlyLimit(),
            'dailyLimit'    => $meter->dailyUserLimit(),
            'tokensToday'   => $meter->usedByUserToday(auth()->user()),
            'cacheDays'     => (int) config('ai.po_scan.cache_days'),
            'maxFiles'      => (int) config('ai.po_scan.max_files'),
            'maxFileMb'     => (int) round(config('ai.po_scan.max_file_kb') / 1024),
        ]);
    }
}
