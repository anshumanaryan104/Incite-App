<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;  
use App\Models\User;  

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Show specified view.
     *
     * @return void
    */
    public function forgetPassword(Request $request)
    { 
        return view('admin.forget-password.index');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return Response
    */
    public function forgetPasswordPost(Request $request)
    {   
        $user = User::adminForgetPassword($request->all());
        if($user['status']==true){
            return redirect()->intended('admin-reset-password')->with('success',$user['message']);
        }
        else{
            return redirect()->back()->withInput($request->only('email'))->with('error', $user['message']);
        }   
    }
}
