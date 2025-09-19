<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;
use anlutro\LaravelSettings\Facade as ContentSetting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $tempArr = array(
            'homepage_theme' => 'home_2',
            'layout' => 'index_1',
            'site_name' => 'NewsCrisp',
            'site_admin_name'=>'Admin NewsCrisp',
            'site_phone' => '+91-7987162771',
            'from_email' => 'info@incite.com',
            'footer_about' => 'The tag defines a footer for a document or section. A element typically contains: authorship information. copyright information. contact information',
            'powered_by' => 'All Rights Reserved Powered by NewsCrisp.',
            'site_seo_title' => 'Incite Flutter App with Admin Panel - Inshort Clone',
            'site_seo_description' => 'Yönetici Paneli ile Flutter Uygulamasını Teşvik Et - Inshort Clone',
            'site_seo_keyword' => 'Flutter Uygulamasını Teşvik Et - Inshort Clone',
            'site_seo_tag' => 'Flutter, Laravel, Web Uygulaması, Mobil Uygulama',
            'preferred_site_language' => 'en',
            'news_api_key' => '',
            'chat_gpt_api_key'=>'',
            'google_translation_api_key'=>'',
            'google_analytics_code' => '',
            'site_logo' => '',
            'website_admin_logo' => '',
            'site_favicon'=>'',
            'app_name' => 'NewsCrisp',
            'bundle_id_android'=>'',
            'bundle_id_ios'=>'',
            'signing_key_android' => '',
            'key_property_android'=>'',            
            'is_app_force_update'=>'',   
            'primary_color'=>'',
            'secondary_color'=>'',         
            'app_logo' => '',
            'rectangualr_app_logo'=>'',
            'app_splash_screen' => '',
            'site_title' => 'NewsCrisp',    
            'enable_notifications' => 0,
            'firebase_msg_key' => '',
            'firebase_api_key' => '',  
            'one_signal_key'=>'',
            'one_signal_app_id'=>'',
            'mailer' => '',
            'host' => '',
            'port' => '',
            'username'=>'',
            'password' => 'test',
            'encryption'=>'',
            'bundle_id_ios'=>'',
            'from_name'=>'',         
            'enable_maintainance_mode'=>0,
            'maintainance_title'=>'Comming Soon!',
            'maintainance_short_text'=>'We are preparing something better & amazing for you.' ,             
            'push_notification_enabled' =>0,
            'date_format' => 'd-m-Y h:i A',
            'timezone' => 'Asia/Kolkata',
            'blog_language'=>'',
            'blog_accent'=>'',
            'blog_voice'=>'',
            'blog_accent_code' => 'uk-UA',            
            'google_api_key'=>'',
            'voice_type'=>'',
            'is_voice_enabled'=>0,
            'live_news_logo' => '',
            'live_news_status' => 0,
            'e_paper_logo' => '',
            'e_paper_status' => 0,            
            'enable_ads' => 0,
            'admob_banner_id_android' => '',
            'admob_interstitial_id_android' => '',
            'admob_banner_id_ios' => '',
            'admob_interstitial_id_ios' => '',
            'admob_frequency' => 0,
            'enable_fb_ads' => 0,
            'fb_ads_placement_id_android' => '',
            'fb_ads_placement_id_ios' => '',
            'fb_ads_app_token' => '',
            'fb_ads_frequency' => 0,
            'fb_ads_interstitial_id_android' => '',
            'fb_ads_interstitial_id_ios' => '',
            'enable_google_login'=>'0',
            'google_client_id'=>'',
            'google_client_secret'=>'',
         );
         
         foreach ($tempArr as $key => $value) {
            Setting::insert([
                 'key' => $key,
                 'value' => $value,
                 'created_at' => date("Y-m-d H:i:s")
            ]);
        }
        $settingsc = Setting::all();
        foreach ($settingsc as $row) {
            ContentSetting::set($row->key, $row->value);
        }
        ContentSetting::save();
    }
}
