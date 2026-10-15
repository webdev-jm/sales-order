<?php

namespace App\Http\Livewire\SalesOrderCancelRequest;

use App\Exceptions\SalesOrderCancelRequestException;
use App\Models\SalesOrderCancelRequest;
use App\Services\SalesOrderCancelRequestService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The actions on one cancel request: the requestor withdraws it while Pending; SCM marks it
 * In progress, then completes it (cancelling the SMS order) or rejects it with remarks.
 */
class Show extends Component
{
    public int $cancelRequestId;

    /** 'complete' or 'reject' while SCM is filling in the remarks. */
    public ?string $action = null;

    public string $remarks = '';

    protected $messages = [
        'remarks.required' => 'Enter the reason for rejecting the request.',
        'remarks.max'      => 'Keep the remarks under 2,000 characters.',
    ];

    public function mount(int $cancelRequestId): void
    {
        $this->cancelRequestId = $cancelRequestId;
    }

    public function startComplete(): void
    {
        $this->action = 'complete';
        $this->resetErrorBag();
    }

    public function startReject(): void
    {
        $this->action = 'reject';
        $this->resetErrorBag();
    }

    public function back(): void
    {
        $this->reset(['action', 'remarks']);
        $this->resetErrorBag();
    }

    public function withdraw(SalesOrderCancelRequestService $service)
    {
        return $this->run(fn (SalesOrderCancelRequest $request) => $service->withdraw($request, auth()->user()), 'You withdrew the request. SCM has been told.');
    }

    public function markInProgress(SalesOrderCancelRequestService $service)
    {
        return $this->run(fn (SalesOrderCancelRequest $request) => $service->markInProgress($request, auth()->user()), 'The request is In progress. The salesman has been told.');
    }

    public function complete(SalesOrderCancelRequestService $service)
    {
        $this->validate(['remarks' => 'nullable|string|max:2000']);

        return $this->run(fn (SalesOrderCancelRequest $request) => $service->complete($request, auth()->user(), $this->remarks), 'The request is completed and the sales order is cancelled. The salesman has been told.');
    }

    public function reject(SalesOrderCancelRequestService $service)
    {
        $this->validate(['remarks' => 'required|string|max:2000']);

        return $this->run(fn (SalesOrderCancelRequest $request) => $service->reject($request, auth()->user(), $this->remarks), 'The request is rejected. The salesman has been told.');
    }

    private function run(callable $step, string $success)
    {
        $request = SalesOrderCancelRequest::findOrFail($this->cancelRequestId);

        try {
            $step($request);
        } catch (SalesOrderCancelRequestException $e) {
            $this->addError('action', $e->getMessage());
            return null;
        }

        return redirect()->route('so-cancel-request.show', $request->id)->with('message_success', $success);
    }

    public function render(SalesOrderCancelRequestService $service): View
    {
        $request = SalesOrderCancelRequest::with(['sales_order.account_login.account', 'user', 'processor'])
            ->findOrFail($this->cancelRequestId);
        $user = auth()->user();

        abort_unless($service->canView($request, $user), 403);

        return view('livewire.sales-order-cancel-request.show')->with([
            'cancelRequest' => $request,
            'canProcess'    => $user->can(SalesOrderCancelRequestService::PROCESS_PERMISSION),
            'isRequestor'   => (int) $request->user_id === (int) $user->id,
        ]);
    }
}
