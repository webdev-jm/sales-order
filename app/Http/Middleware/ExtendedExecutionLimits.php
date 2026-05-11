<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ExtendedExecutionLimits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        ini_set('sqlsrv.ClientBufferMaxKBSize', '1000000');
        ini_set('pdo_sqlsrv.client_buffer_max_kb_size', '1000000');

        return $next($request);
    }
}
