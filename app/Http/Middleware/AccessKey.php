<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class AccessKey
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->header('access-token') && $request->header('access-token') == Config::get('auth.access_token')) {
            return $next($request);
        }

        return response()->json('Missing access token', Response::HTTP_UNAUTHORIZED);
    }
}
