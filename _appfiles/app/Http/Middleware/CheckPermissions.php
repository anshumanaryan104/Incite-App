<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class CheckPermissions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $permission = $this->getPermissionFromRequest($request);
        // echo json_encode($permission);exit;  
        // if (Auth::check() && !Auth::user()->can($permission)) {
        //     abort(403); // Or redirect to a custom unauthorized page
        // }

        return $next($request);
    }

    private function getPermissionFromRequest($request)
    {
        // Logic to extract the permission from the request
        // You can customize this as per your requirements
        // For example, you can extract the permission from the URL, query parameters, or any other request data.

        // Example: Extract permission from the URL segment
        $segments = $request->segment('2');
        $permission = $segments; // Get the last segment of the URL as the permission

        return $permission;
    }
}
