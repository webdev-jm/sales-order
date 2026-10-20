<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBranchLocationRequest;
use App\Models\BranchLogin;
use App\Services\Maps\LocationTrail;
use Illuminate\Http\JsonResponse;

class BranchLocationController extends Controller
{
    /**
     * Save GPS readings the browser kept for a branch login (see partials/branch-location-tracker).
     *
     * With branch_login_id the readings go to that branch login of the user, even after sign-out, so a phone
     * that was offline can catch up. Without it they go to the user's open branch login. 409 means there is no
     * such branch login: the browser then discards those readings.
     */
    public function store(StoreBranchLocationRequest $request): JsonResponse
    {
        $branchLogin = BranchLogin::where('user_id', auth()->id())
            ->when(
                $request->filled('branch_login_id'),
                fn ($query) => $query->whereKey($request->integer('branch_login_id'))
                    ->where('time_in', '>=', now()->subDays(LocationTrail::LATE_SYNC_DAYS)),
                fn ($query) => $query->whereNull('time_out')->latest('time_in')
            )
            ->first();

        if (empty($branchLogin)) {
            return response()->json(['recorded' => false, 'reason' => 'no_branch_login'], 409);
        }

        $result = LocationTrail::save($branchLogin, $request->rawReadings());

        return response()->json(['recorded' => true] + $result);
    }
}
