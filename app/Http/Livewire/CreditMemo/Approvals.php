<?php

namespace App\Http\Livewire\CreditMemo;

use App\Models\CreditMemoRemarks;
use Livewire\Component;
use App\Http\Livewire\Traits\WithCreditMemoStatus;
use App\Models\CreditMemoApproval;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\Http\Traits\CreditMemoXml;
use Illuminate\Support\Facades\Storage;

class Approvals extends Component
{
    use WithCreditMemoStatus, CreditMemoXml;

    public $creditMemo;
    public $canReview;
    public $canApprove;
    public $message;

    public function mount($creditMemo)
    {
        $this->creditMemo = $creditMemo;
        $this->canReview = Gate::allows('cm review');
        $this->canApprove = Gate::allows('cm approve');
    }

    public function approve($status)
    {
        DB::transaction(function () use ($status) {
            $old = $this->creditMemo->getOriginal();
            $this->creditMemo->update(['status' => $status]);

            CreditMemoApproval::create([
                'credit_memo_id' => $this->creditMemo->id,
                'user_id' => auth()->id(),
                'status' => $status,
            ]);

            if ($status === 'approved') {
                $this->rud = $this->creditMemo;
                $xmls = $this->generateCreditMemoXmls();

                $directory = 'credit_memos/rud_' . $this->rud->id;
                foreach ($xmls as $key => $xmlContent) {
                    // Convert keys like 'sortci_xml' to 'SORTCI.xml'
                    $fileName = strtoupper(str_replace('_xml', '', $key)) . '.xml';

                    // Save the file to the local disk (storage/app/...)
                    Storage::disk('local')->put($directory . '/' . $fileName, $xmlContent);
                }
            }

            activity('updated')->performedOn($this->creditMemo)
                ->log(':causer.firstname has ' . $status . ' RUD invoice ' . $this->creditMemo->invoice_number);
        });

        $this->emit('updateHistory');
    }

    public function saveRemarks() {
        $this->validate([
            'message' => [
                'required'
            ]
        ]);

        $cm_remark = new CreditMemoRemarks([
            'credit_memo_id' => $this->creditMemo->id,
            'user_id' => auth()->user()->id,
            'message' => $this->message,
            'seen_by' => NULL
        ]);
        $cm_remark->save();

        $this->reset('message');

        $this->emit('remarkAdded');
    }

    public function render()
    {
        return view('livewire.credit-memo.approvals');
    }
}
