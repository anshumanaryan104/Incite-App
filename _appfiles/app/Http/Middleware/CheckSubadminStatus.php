<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;
use App\Models\User;

class CheckSubadminStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = User::where('id',Auth::user()->id)->first();
            if(!$user){
                Auth::logout();
                return redirect('/admin-login')->with('error', 'Your account has been deactivated by the admin.');
            }else{
                if($user->status==0){
                    Auth::logout();
                    return redirect('/admin-login')->with('error', 'Your account has been deactivated by the admin.');
                }
            }
        }

        return $next($request);
    }
}
