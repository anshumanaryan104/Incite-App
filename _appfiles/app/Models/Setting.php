<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use anlutro\LaravelSettings\Facade as ContentSetting;
use Illuminate\Support\Facades\Session;

class Setting extends Model
{
    use HasFactory;
    protected $table = "settings";

    /**
     * update
    **/
    public static function updateContent($data)
    {        
        try 
        {
            $obj = new self;
            unset($data['_token']);
            $page_name = $data['page_name'];
            unset($data['page_name']);
            if($page_name=='push-notification-setting'){
                if (isset($data['enable_notifications'])) {
                    if($data['enable_notifications']=='on'){
                    $data['enable_notifications'] = 1;
                    }else{
                    $data['enable_notifications'] = $data['enable_notifications'];
                    }
                }else{
                    $data['enable_notifications'] = 0;
                }
            }
            if($page_name=='admob-setting'){
                if (isset($data['enable_ads'])) {
                    if($data['enable_ads']=='on'){
                    $data['enable_ads'] = 1;
                    }else{
                    $data['enable_ads'] = $data['enable_ads'];
                    }
                }else{
                    $data['enable_ads'] = 0;
                }
            }
            if($page_name=='fb-ads-setting'){
                if (isset($data['enable_fb_ads'])) {
                    if($data['enable_fb_ads']=='on'){
                    $data['enable_fb_ads'] = 1;
                    }else{
                    $data['enable_fb_ads'] = $data['enable_fb_ads'];
                    }
                }else{
                    $data['enable_fb_ads'] = 0;
                }
            }
            if($page_name=='app-setting'){
                if (isset($data['is_app_force_update'])) {
                    if($data['is_app_force_update']=='on'){
                    $data['is_app_force_update'] = 1;
                    }else{
                    $data['is_app_force_update'] = $data['is_app_force_update'];
                    }
                }else{
                    $data['is_app_force_update'] = 0;
                }
            }
            if($page_name=='news-setting'){
                if (isset($data['live_news_status'])) {
                    if($data['live_news_status']=='on'){
                    $data['live_news_status'] = 1;
                    }else{
                    $data['live_news_status'] = $data['live_news_status'];
                    }
                }else{
                    $data['live_news_status'] = 0;
                }
                if (isset($data['e_paper_status'])) {
                    if($data['e_paper_status']=='on'){
                    $data['e_paper_status'] = 1;
                    }else{
                    $data['e_paper_status'] = $data['e_paper_status'];
                    }
                }else{
                    $data['e_paper_status'] = 0;
                }
            }
            if($page_name=='global-setting'){
                if (isset($data['is_voice_enabled'])) {
                    if($data['is_voice_enabled']=='on'){
                    $data['is_voice_enabled'] = 1;
                    }else{
                    $data['is_voice_enabled'] = $data['is_voice_enabled'];
                    }
                }else{
                    $data['is_voice_enabled'] = 0;
                }
            }
            if($page_name=='maintainance-setting'){
                if (isset($data['enable_maintainance_mode'])) {
                    if($data['enable_maintainance_mode']=='on'){
                        $data['enable_maintainance_mode'] = 1;
                    }else{
                        $data['enable_maintainance_mode'] = $data['enable_maintainance_mode'];
                    }
                }else{
                    $data['enable_maintainance_mode'] = 0;
                }
            }
            if($page_name=='social-media-login-setting'){
                if (isset($data['enable_google_login'])) {
                    if($data['enable_google_login']=='on'){
                        $data['enable_google_login'] = 1;
                    }else{
                        $data['enable_google_login'] = $data['enable_google_login'];
                    }
                }else{
                    $data['enable_google_login'] = 0;
                }
            }
            // echo json_encode($data);exit;
            if(isset($data['site_logo']) && $data['site_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['site_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['site_logo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['website_admin_logo']) && $data['website_admin_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['website_admin_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['website_admin_logo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['site_favicon']) && $data['site_favicon']!=''){
                $uploadImage = \Helpers::uploadFiles($data['site_favicon'],'setting/');
                if($uploadImage['status']==true){
                    $data['site_favicon'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['app_logo']) && $data['app_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['app_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['app_logo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['app_splash_screen']) && $data['app_splash_screen']!=''){
                $uploadImage = \Helpers::uploadFiles($data['app_splash_screen'],'setting/');
                if($uploadImage['status']==true){
                    $data['app_splash_screen'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['live_news_logo']) && $data['live_news_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['live_news_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['live_news_logo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['e_paper_logo']) && $data['e_paper_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['e_paper_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['e_paper_logo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['rectangualr_app_logo']) && $data['rectangualr_app_logo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['rectangualr_app_logo'],'setting/');
                if($uploadImage['status']==true){
                    $data['rectangualr_app_logo'] = $uploadImage['file_name'];
                }
            }
            foreach ($data as $key => $value) {
                
                $exist = $obj->where('key',$key)->first();
                if ($exist) {
                    $id = $obj->where('id',$exist->id)->update(array('value'=>$value));
                }else{
                    $obj->insert(array('key'=>$key,'value'=>$value));
                }
            }

            $settingsc = $obj->all();
            foreach ($settingsc as $row) {
                ContentSetting::set($row->key, $row->value);
            }
            ContentSetting::save();
            $envFilePath = base_path('.env');
            $replacementPairs = array();
            if(isset($data['google_client_id']) && $data['google_client_id']!=''){
                $replacementPairs['GOOGLE_CLIENT_ID'] = $data['google_client_id'];
            }
            if(isset($data['google_client_secret']) && $data['google_client_secret']!=''){
                $replacementPairs['GOOGLE_CLIENT_SECRET'] = $data['google_client_secret'];
            }
            if(isset($data['mailer']) && $data['mailer']!=''){
                $replacementPairs['MAIL_MAILER'] = $data['mailer'];
            }
            if(isset($data['host']) && $data['host']!=''){
                $replacementPairs['MAIL_HOST'] = $data['host'];
            }
            if(isset($data['port']) && $data['port']!=''){
                $replacementPairs['MAIL_PORT'] = $data['port'];
            }
            if(isset($data['username']) && $data['username']!=''){
                $replacementPairs['MAIL_USERNAME'] = $data['username'];
            }
            if(isset($data['password']) && $data['password']!=''){
                $replacementPairs['MAIL_PASSWORD'] = $data['password'];
            }
            if(isset($data['encryption']) && $data['encryption']!=''){
                $replacementPairs['MAIL_ENCRYPTION'] = $data['encryption'];
            }
            if(isset($data['from_name']) && $data['from_name']!=''){
                $replacementPairs['MAIL_FROM_NAME'] = $data['from_name'];
            } 
            $envContents = file_get_contents($envFilePath);
            if(count($replacementPairs)>0){
                foreach ($replacementPairs as $key => $value) {
                    $search = "{$key}=";
                    $replacement = "{$key}={$value}";
                    $envContents = preg_replace("/^{$key}=.*/m", $replacement, $envContents);
                }
                
                file_put_contents($envFilePath, $envContents);
            }        

            if (isset($post['blog_language'])) {
                Session::put('locale', $post['blog_language']);             
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        } 
        catch (\Exception $e) 
        {
    		return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
    	}
    }
}
