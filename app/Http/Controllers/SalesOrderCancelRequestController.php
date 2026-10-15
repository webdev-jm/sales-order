<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderCancelRequest;
use App\Services\SalesOrderCancelRequestService;
use Illuminate\Contracts\View\View;

class SalesOrderCancelRequestController extends Controller
{
    public function index(): View
    {
        return view('pages.so-cancel-requests.index');
    }

    public function show(int $id, SalesOrderCancelRequestService $service): View
    {
        $cancelRequest = SalesOrderCancelRequest::findOrFail($id);

        abort_unless($service->canView($cancelRequest, auth()->user()), 403);

        return view('pages.so-cancel-requests.show')->with([
            'cancelRequest' => $cancelRequest,
        ]);
    }
}
