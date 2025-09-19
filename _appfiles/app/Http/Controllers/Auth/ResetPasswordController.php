<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

use App\Models\User;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Show specified view.
     *
     * @return void
    */
    public function resetPassword(Request $request)
    { 
        return view('admin.reset-password.index');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
    */
    public function resetPasswordPost(Request $request)
    {   
        $data = User::adminResetPassword($request->all());
        if($data['status']==true){
            return redirect()->intended('admin-login')->with('success',$data['message']);
        }
        else{
            return redirect()->back()->withInput($request->only('email'))->with('error', $data['message']);
        }   
    }
}
