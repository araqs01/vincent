<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Localization
{
    public function handle(Request $request, Closure $next)
    {
        if (!empty($request->header('x-localization'))) {
            app()->setLocale($request->header('x-localization'));
        }

        return $next($request);
    }
}
