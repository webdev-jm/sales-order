<?php

namespace App\Http\Livewire\SalesOrderCancelRequest;

use App\Models\SalesOrderCancelRequest;
use App\Services\SalesOrderCancelRequestService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * The SO Cancel Requests list. SCM sees every request; a salesman sees the ones they filed.
 */
class Index extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $status = '';

    public string $search = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(string $status): void
    {
        $this->status = array_key_exists($status, SalesOrderCancelRequest::STATUS_COLORS) ? $status : '';
        $this->resetPage();
    }

    private function visibleRequests(): Builder
    {
        $user = auth()->user();

        return SalesOrderCancelRequest::query()
            ->when(!$user->can(SalesOrderCancelRequestService::PROCESS_PERMISSION), fn (Builder $query) => $query->where('user_id', $user->id));
    }

    public function render(): View
    {
        $search = trim($this->search);

        $requests = $this->visibleRequests()
            ->with(['sales_order.account_login.account', 'user', 'processor'])
            ->when($this->status !== '', fn (Builder $query) => $query->where('status', $this->status))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('request_number', 'like', '%' . $search . '%')
                        ->orWhereHas('sales_order', function (Builder $query) use ($search) {
                            $query->withTrashed()->where(function (Builder $query) use ($search) {
                                $query->where('control_number', 'like', '%' . $search . '%')
                                    ->orWhere('po_number', 'like', '%' . $search . '%')
                                    ->orWhere('reference', 'like', '%' . $search . '%');
                            });
                        })
                        ->orWhereHas('user', function (Builder $query) use ($search) {
                            $query->where('firstname', 'like', '%' . $search . '%')
                                ->orWhere('lastname', 'like', '%' . $search . '%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->onEachSide(1);

        $counts = $this->visibleRequests()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.sales-order-cancel-request.index')->with([
            'requests' => $requests,
            'counts'   => $counts,
        ]);
    }
}
