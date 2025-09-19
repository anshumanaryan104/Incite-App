<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Beycan\Envato\EnvatoChecker;

class LicenseVerificationController extends Controller
{
    public function showVerificationForm()
    {
        return view('verify-purchase');
    }

    public function verify(Request $request)
    {
        $checker = new EnvatoChecker();
        $purchaseCode = $request->input('purchase_code');
        $result = $checker->check($purchaseCode);

        if ($result->isValid()) {
            // Valid license code
            return redirect('/dashboard');
        } else {
            // Invalid license code
            return back()->withErrors(['purchase_code' => 'Invalid purchase code']);
        }
    }
}
