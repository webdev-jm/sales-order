<?php

namespace App\Http\Controllers;

use App\Models\BranchLogin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchLocationController extends Controller
{
    /**
     * Maximum points kept per branch login. At one point a minute this covers
     * roughly a 24 hour shift; older points are trimmed to keep the JSON small.
     */
    private const MAX_POINTS = 1500;

    /**
     * Append a geolocation point to the authenticated user's open branch login.
     * Called every minute by the client-side tracker while a branch session is
     * active. Recording stops naturally once the branch login is timed out.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy'  => ['nullable'],
        ]);

        $branch_login = BranchLogin::where('user_id', auth()->id())
            ->whereNull('time_out')
            ->latest('time_in')
            ->first();

        if (empty($branch_login)) {
            return response()->json(['recorded' => false, 'reason' => 'no_active_login'], 409);
        }

        $trail = $branch_login->location_trail ?? [];

        $trail[] = [
            'latitude'    => (float) $validated['latitude'],
            'longitude'   => (float) $validated['longitude'],
            'accuracy'    => $validated['accuracy'] ?? null,
            'recorded_at' => now()->toDateTimeString(),
        ];

        if (count($trail) > self::MAX_POINTS) {
            $trail = array_slice($trail, -self::MAX_POINTS);
        }

        $branch_login->update(['location_trail' => $trail]);

        return response()->json([
            'recorded' => true,
            'points'   => count($trail),
        ]);
    }
}
