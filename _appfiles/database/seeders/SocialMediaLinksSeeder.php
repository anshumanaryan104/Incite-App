<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SocialMediaLink;

class SocialMediaLinksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $social_media_links = array(
            array('name' => 'Whatsapp','url' => 'https://whatsapp.com?url=whatsapp://','icon' => 'fa-whatsapp','icon_background_color' => '#075e54','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'fb','url' => 'https://facebook.com?url=fb://','icon' => 'fa-facebook','icon_background_color' => NULL,'icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Instagram','url' => 'https://instagram.com?url=instagram://','icon' => 'fa-instagram','icon_background_color' => '#E1306C','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'LinkedIn','url' => 'https://www.linkedin.com/home?originalSubdomain=in','icon' => 'fa-linkedin','icon_background_color' => '#0A66C2','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'YouTube','url' => 'https://www.youtube.com/','icon' => 'fa-youtube','icon_background_color' => '#CD201F','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'TikTok','url' => 'https://www.google.com/','icon' => 'brand-tiktok','icon_background_color' => NULL,'icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Telegram','url' => 'https://web.telegram.org/','icon' => 'fa-telegram','icon_background_color' => '#0088cc','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Snapchat','url' => 'https://www.snapchat.com/','icon' => 'fa-snapchat','icon_background_color' => '#FFFC00','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Twitter','url' => 'https://twitter.com/','icon' => 'fa-twitter','icon_background_color' => '#00acee','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Reddit','url' => 'https://www.reddit.com/','icon' => 'fa-reddit','icon_background_color' => '#FF5700','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Pintrest','url' => 'https://in.pinterest.com/','icon' => 'fa-pinterest','icon_background_color' => '#E60023','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Skype','url' => 'https://join.skype.com/','icon' => 'fa-skype','icon_background_color' => '#00aff0','icon_text_color' => NULL,'status' => '0'),
            array('name' => 'Discord','url' => 'https://discord.com/','icon' => 'brand-discord','icon_background_color' => NULL,'icon_text_color' => NULL,'status' => '0')
        );
        foreach ($social_media_links as $row) {
            SocialMediaLink::insert([
                 'name' => $row['name'],
                 'url' => $row['url'],
                 'icon' => $row['icon'],
                 'icon_background_color' => $row['icon_background_color'],
                 'icon_text_color' => $row['icon_text_color'],
                 'status' => $row['status'],
                 'created_at' => date("Y-m-d H:i:s")
            ]);
        }
    }
}
