<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use beycanpress\Envato\EnvatoChecker;
// use BeycanPress\EnvatoLicenseChecker\EnvatoChecker;
use BeycanPress\EnvatoLicenseChecker;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use App\Models\Settings;

class LicenseController extends Controller
{
    //
    public function index()
    {
        return view('license.verify');
    }

    public function verify(Request $request)
    {
        $client = new Client();
        $response = $client->request('GET', 'http://license.technofox.co.in/api/verify-code?code='.$request->purchase_code);
        $data = json_decode($response->getBody());
        if ($data->status==true) {
            // An error occurred
            File::put(base_path('.env'), str_replace(
                'CODE_VERIFIED=',
                'CODE_VERIFIED=true',
                file_get_contents(base_path('.env'))
            ));
            return redirect()->back()->with('success',"Code verified successfully.");
        } else {
            // return $data;
            return redirect()->back()->withInput($request->only('purchase_code'))->with('error', $data->message);
        }
    }
}
