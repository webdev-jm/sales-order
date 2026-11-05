<?php

namespace App\Http\Middleware;

use App\Support\MaintenanceSchedule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * The maintenance page, switched on with MAINTENANCE_MODE in .env, or by itself
 * at MAINTENANCE_STARTS_AT (see config/maintenance.php and MaintenanceSchedule),
 * for when `php artisan down` cannot be run on the server. Everyone gets the
 * page with a 503, except the addresses allowed and anyone who opened the
 * secret link. API callers get a JSON 503.
 *
 * It runs before the session and the database, so the page works whatever
 * is being maintained.
 */
class EnvMaintenanceMode
{
    public function __construct(private MaintenanceSchedule $schedule)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->schedule->isOn()) {
            return $next($request);
        }

        if (in_array($request->ip(), (array) config('maintenance.allowed_ips', []), true)) {
            return $this->letIn($request, $next);
        }

        $secret = (string) config('maintenance.secret');
        $cookie = (string) config('maintenance.cookie');

        if ($secret !== '') {
            $pass = hash_hmac('sha256', 'maintenance-pass', $secret.config('app.key'));

            if (hash_equals($secret, (string) $request->query('maintenance', ''))) {
                return redirect($request->fullUrlWithoutQuery('maintenance'))
                    ->withCookie(new Cookie($cookie, $pass, now()->addHours(12), '/', null, $request->isSecure(), true, false, Cookie::SAMESITE_LAX));
            }

            if (hash_equals($pass, (string) $request->cookies->get($cookie, ''))) {
                return $this->letIn($request, $next);
            }
        }

        $retryAfter = (int) config('maintenance.retry_after', 300);

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->message()], 503, ['Retry-After' => $retryAfter]);
        }

        return response()->view('maintenance.page', [
            'message' => $this->message(),
            'until' => $this->schedule->until(),
        ], 503, ['Retry-After' => $retryAfter, 'Cache-Control' => 'no-store']);
    }

    /**
     * Someone allowed in while it is on sees a strip saying so (maintenance/warning).
     */
    private function letIn(Request $request, Closure $next): Response
    {
        $request->attributes->set(MaintenanceSchedule::BYPASS_ATTRIBUTE, true);

        return $next($request);
    }

    private function message(): string
    {
        return trim((string) config('maintenance.message')) ?: __('The system is being updated. It will be back shortly.');
    }
}
