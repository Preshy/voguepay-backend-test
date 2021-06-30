<?php

namespace App\Http\Middleware;

use Closure;

class AppMiddleware
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

        $secret = $request->header('secret');
        
        if(!$secret) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Secret not provided.'
            ], 401);
        }

        if($secret <> env('APP_SECRET')) {
            // Unauthorized response if token not there
            return response()->json([
                'error' => 'Secret not valid.'
            ], 401);
        }

        // Pre-Middleware Action

        $response = $next($request);

        // Post-Middleware Action

        return $response;
    }
}
