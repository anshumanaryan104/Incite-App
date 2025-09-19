<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headerData = ($request->header('api-token')? $request->header('api-token'):$request->header('api-token'));
        $tokenData = \Helpers::validateAuthToken($headerData);
        if (! $tokenData) {
            $response = [
                'success' => false,
                'message' => 'Unauthenticated',
            ];
            return response($response, 401);
        }
        $request->userAuthData = $tokenData;
        return $next($request);
    }
}
