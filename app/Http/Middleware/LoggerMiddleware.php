<?php

namespace App\Http\Middleware;

use App\Utility;
use Closure;

class LoggerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Utility::log($request);
        return $next($request);
    }
}
