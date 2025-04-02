<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\TransientToken;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Carbon;

class CheckForExpiredToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && 
            $request->user()->currentAccessToken() && 
            !$request->user()->currentAccessToken() instanceof TransientToken) {
            
            $expiration = config('sanctum.expiration');
            
            if ($expiration) {
                $tokenCreatedAt = Carbon::parse(
                    $request->user()->currentAccessToken()->created_at
                );
                
                if (Carbon::now()->diffInMinutes($tokenCreatedAt) >= $expiration) {
                    return response()->json([
                        'message' => 'Token has expired',
                        'expired_at' => Carbon::now()->subMinutes($expiration)->toDateTimeString(),
                        'please' => 'Login again to get a new token'
                    ], 401);
                }
            }
        }
        
        return $next($request);
    }
}
