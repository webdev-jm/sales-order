<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check()) {
            $userId = Auth::id();

            Cache::put('user-is-online-' . $userId, true, now()->addMinutes(2));

            if (!Cache::has('user-activity-written-' . $userId)) {
                User::where('id', $userId)->update(['last_activity' => now()]);
                Cache::put('user-activity-written-' . $userId, true, now()->addMinutes(1));
            }
        }

        return $next($request);
    }
}
