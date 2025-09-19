<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\Setting;
use App\Models\Language;
use App\Models\Visibility;
use App\Http\Requests\StoreSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data['result'] = Setting::get();
            $data['languages'] = Language::where('status',1)->get();
            $data['voice_accent'] = config('constant.voice_accent');
            $data['zones'] = timezone_identifiers_list();
            $data['font_family'] = config('constant.font_family');
            return view('admin/setting.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {        
        try{
            $updated = Setting::updateContent($request->all());
            if($updated['status']){
                return redirect()->back()->with('success', $updated['message']); 
            }
            else{
                return redirect()->back()->with('error', $updated['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }        
    }

    /**
     * Set language.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setLanguage(Request $request){
        $post = $request->all();
        if (array_key_exists($post['lang'], Config::get('languages'))) {
            if (isset($post['lang'])) {
                App::setLocale($post['lang']);
                Session::put('admin_locale', $post['lang']);
                setcookie('admin_lang_code',$post['lang'],time()+60*60*24*365);
            }
        }
        return redirect()->back();
    }
}
