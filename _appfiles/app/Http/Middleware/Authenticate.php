<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Auth;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        
        if (Session()->has('locale') AND array_key_exists(Session()->get('locale'), config('languages'))) {
            App::setLocale(Session()->get('locale'));
        }
        else { // This is optional as Laravel will automatically set the fallback language if there is none specified
            App::setLocale(config('app.fallback_locale'));
        }
        
        // $tokenData = \Helpers::validateAuthToken($headerData);
        // if (! $tokenData) {
        //     $response = [
        //         'success' => false,
        //         'message' => 'Unauthenticated',
        //     ];
        //     return response($response, 401);
        // }
        return $request->expectsJson() ? null : url('admin-login');
    }
}
