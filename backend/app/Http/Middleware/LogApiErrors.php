<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Log errors for responses with status codes >= 400
        if ($response->status() >= 400) {
            Log::error('API Error', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status' => $response->status(),
                'response' => $response->getContent(),
                'headers' => $request->headers->all(),
                'body' => $request->all(),
            ]);
        }

        return $response;
    }
}
