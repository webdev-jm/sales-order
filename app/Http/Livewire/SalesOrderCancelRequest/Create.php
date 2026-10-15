<?php

namespace App\Http\Livewire\SalesOrderCancelRequest;

use App\Exceptions\SalesOrderCancelRequestException;
use App\Models\SalesOrder;
use App\Services\SalesOrderCancelRequestService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * The cancel request form. On the sales order page it is mounted with that order and only asks
 * for the reason; on the SO Cancel Requests page the salesman first picks one of their orders
 * that is already in SYSPRO.
 */
class Create extends Component
{
    /** Set when the form sits on a sales order page. */
    public ?int $salesOrderId = null;

    public ?int $selectedOrderId = null;

    public string $search = '';

    public string $reason = '';

    public bool $showForm = false;

    protected $messages = [
        'selectedOrderId.required' => 'Choose the sales order to cancel.',
        'reason.required'          => 'Enter why the order should be cancelled.',
        'reason.max'               => 'Keep the reason under 2,000 characters.',
    ];

    public function mount(?int $salesOrderId = null): void
    {
        $this->salesOrderId    = $salesOrderId;
        $this->selectedOrderId = $salesOrderId;
    }

    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'selectedOrderId' => 'required|integer',
            'reason'          => 'required|string|max:2000',
        ];
    }

    public function open(): void
    {
        $this->showForm = true;
    }

    public function close(): void
    {
        $this->reset(['reason', 'search', 'showForm']);
        $this->selectedOrderId = $this->salesOrderId;
        $this->resetErrorBag();
    }

    public function selectOrder(int $orderId): void
    {
        $this->selectedOrderId = $orderId;
        $this->resetErrorBag('selectedOrderId');
    }

    public function submit(SalesOrderCancelRequestService $service)
    {
        $this->validate();

        $salesOrder = SalesOrder::find($this->selectedOrderId);
        if (!$salesOrder) {
            $this->addError('selectedOrderId', 'That sales order no longer exists.');
            return null;
        }

        try {
            $cancelRequest = $service->submit($salesOrder, auth()->user(), $this->reason);
        } catch (SalesOrderCancelRequestException $e) {
            $this->addError('selectedOrderId', $e->getMessage());
            return null;
        }

        return redirect()->route('so-cancel-request.show', $cancelRequest->id)
            ->with('message_success', 'Cancel request ' . $cancelRequest->request_number . ' was sent to SCM.');
    }

    public function render(SalesOrderCancelRequestService $service): View
    {
        $user       = auth()->user();
        $fixedOrder = $this->salesOrderId ? SalesOrder::with('account_login.account')->find($this->salesOrderId) : null;

        $orders        = collect();
        $selectedOrder = null;

        if (!$fixedOrder && $this->showForm) {
            $orders = $service->eligibleOrders($user, $this->search)->limit(8)->get();

            if ($this->selectedOrderId) {
                $selectedOrder = $orders->firstWhere('id', $this->selectedOrderId)
                    ?? $service->eligibleOrders($user)->whereKey($this->selectedOrderId)->first();
            }
        }

        return view('livewire.sales-order-cancel-request.create')->with([
            'fixedOrder'       => $fixedOrder,
            'eligibilityError' => $fixedOrder ? $service->eligibilityError($fixedOrder, $user) : null,
            'openRequest'      => $fixedOrder?->open_cancel_request()->first(),
            'orders'           => $orders,
            'selectedOrder'    => $selectedOrder ?? $fixedOrder,
            'canCreate'        => $user->can(SalesOrderCancelRequestService::CREATE_PERMISSION),
        ]);
    }
}
