<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tempArr = array(
            array('module' => 'Dashboard','route_name' => 'dashboard','permission_name' => 'Dashboard','group' => 'dashboard','is_default' => 1),
            array('module' => 'Category','route_name' => 'category','permission_name' => 'List','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'add-category','permission_name' => 'Add','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'update-category','permission_name' => 'Update','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'update-category-column','permission_name' => 'Status Change','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'delete-category','permission_name' => 'Delete','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'translation-category','permission_name' => 'Translation','group' => 'category','is_default' => 0),
            array('module' => 'Category','route_name' => 'translation-by-third-party','permission_name' => 'Translate Externally','group' => 'category','is_default' => 0),
            array('module' => 'Live News','route_name' => 'live-news','permission_name' => 'List','group' => 'live-news','is_default' => 0),
            array('module' => 'Live News','route_name' => 'add-live-news','permission_name' => 'Add','group' => 'live-news','is_default' => 0),
            array('module' => 'Live News','route_name' => 'update-live-news','permission_name' => 'Update','group' => 'live-news','is_default' => 0),
            array('module' => 'Live News','route_name' => 'update-live-news-status','permission_name' => 'Status Change','group' => 'live-news','is_default' => 0),
            array('module' => 'Live News','route_name' => 'delete-live-news','permission_name' => 'Delete','group' => 'live-news','is_default' => 0),
            array('module' => 'Live News','route_name' => 'translation-live-news','permission_name' => 'Translation','group' => 'live-news','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'e-papers','permission_name' => 'List','group' => 'e-papers','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'add-e-paper','permission_name' => 'Add','group' => 'e-papers','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'update-e-paper','permission_name' => 'Update','group' => 'e-papers','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'update-e-paper-status','permission_name' => 'Status Change','group' => 'e-papers','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'delete-e-paper','permission_name' => 'Delete','group' => 'e-papers','is_default' => 0),
            array('module' => 'E-Paper','route_name' => 'translation-e-paper','permission_name' => 'Translation','group' => 'e-papers','is_default' => 0),
            array('module' => 'CMS','route_name' => 'cms','permission_name' => 'List','group' => 'cms','is_default' => 0),
            array('module' => 'CMS','route_name' => 'add-cms','permission_name' => 'Add','group' => 'cms','is_default' => 0),
            array('module' => 'CMS','route_name' => 'update-cms','permission_name' => 'Update','group' => 'cms','is_default' => 0),
            array('module' => 'CMS','route_name' => 'update-cms-status','permission_name' => 'Status Change','group' => 'cms','is_default' => 0),
            array('module' => 'CMS','route_name' => 'delete-cms','permission_name' => 'Delete','group' => 'cms','is_default' => 0),
            array('module' => 'CMS','route_name' => 'translation-cms','permission_name' => 'Translation','group' => 'cms','is_default' => 0),
            array('module' => 'User','route_name' => 'user','permission_name' => 'List','group' => 'user','is_default' => 0),
            array('module' => 'User','route_name' => 'update-user-status','permission_name' => 'Status Change','group' => 'user','is_default' => 0),
            array('module' => 'User','route_name' => 'delete-user','permission_name' => 'Delete','group' => 'user','is_default' => 0),
            array('module' => 'User','route_name' => 'personlization','permission_name' => 'Personalization','group' => 'user','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'sub-admin','permission_name' => 'List','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'add-sub-admin','permission_name' => 'Add','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'update-sub-admin','permission_name' => 'Update','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'update-sub-admin-status','permission_name' => 'Status Change','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'delete-sub-admin','permission_name' => 'Delete','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Subadmin','route_name' => 'sub-admin-translation','permission_name' => 'Translation','group' => 'sub-admin','is_default' => 0),
            array('module' => 'Setting','route_name' => 'settings','permission_name' => 'Setting','group' => 'settings','is_default' => 0),
            array('module' => 'Setting','route_name' => 'update-setting','permission_name' => 'Update','group' => 'settings','is_default' => 0),
            array('module' => 'Social Media','route_name' => 'social-media','permission_name' => 'List','group' => 'social-media','is_default' => 0),
            array('module' => 'Social Media','route_name' => 'add-social-media','permission_name' => 'Add','group' => 'social-media','is_default' => 0),
            array('module' => 'Social Media','route_name' => 'update-social-media','permission_name' => 'Update','group' => 'social-media','is_default' => 0),
            array('module' => 'Social Media','route_name' => 'update-social-media-status','permission_name' => 'Status Change','group' => 'social-media','is_default' => 0),
            array('module' => 'Social Media','route_name' => 'delete-social-media','permission_name' => 'Delete','group' => 'social-media','is_default' => 0),
            array('module' => 'Ads','route_name' => 'ads','permission_name' => 'List','group' => 'ads','is_default' => 0),
            array('module' => 'Ads','route_name' => 'add-ad','permission_name' => 'Add','group' => 'ads','is_default' => 0),
            array('module' => 'Ads','route_name' => 'update-ad','permission_name' => 'Update','group' => 'ads','is_default' => 0),
            array('module' => 'Ads','route_name' => 'update-ad-status','permission_name' => 'Status Change','group' => 'ads','is_default' => 0),
            array('module' => 'Ads','route_name' => 'delete-ad','permission_name' => 'Delete','group' => 'ads','is_default' => 0),
            array('module' => 'Ads','route_name' => 'ads-sortable','permission_name' => 'Sortable','group' => 'ads','is_default' => 0),
            array('module' => 'Blog','route_name' => 'blog','permission_name' => 'List','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'add-blog','permission_name' => 'Add','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'update-blog','permission_name' => 'Update','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'update-blog-status','permission_name' => 'Status Change','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'delete-blog','permission_name' => 'Delete','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'blog-translation','permission_name' => 'Translation','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'publish-blog','permission_name' => 'Publish','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'send-notification','permission_name' => 'Notification','group' => 'blog','is_default' => 0),
            array('module' => 'Blog','route_name' => 'analytics','permission_name' => 'Analytics','group' => 'blog','is_default' => 0),
            array('module' => 'Role','route_name' => 'role','permission_name' => 'List','group' => 'role','is_default' => 0),
            array('module' => 'Role','route_name' => 'add-role','permission_name' => 'Add','group' => 'role','is_default' => 0),
            array('module' => 'Role','route_name' => 'update-role','permission_name' => 'Update','group' => 'role','is_default' => 0),
            array('module' => 'Role','route_name' => 'update-role-status','permission_name' => 'Status Change','group' => 'role','is_default' => 0),
            array('module' => 'Role','route_name' => 'delete-role','permission_name' => 'Delete','group' => 'role','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'visibility','permission_name' => 'List','group' => 'visibility','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'add-visibility','permission_name' => 'Add','group' => 'visibility','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'update-visibility','permission_name' => 'Update','group' => 'visibility','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'update-visibility-status','permission_name' => 'Status Change','group' => 'visibility','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'delete-visibility','permission_name' => 'Delete','group' => 'visibility','is_default' => 0),
            array('module' => 'Visibility','route_name' => 'visibility-translation','permission_name' => 'Translation','group' => 'visibility','is_default' => 0),
            array('module' => 'News API','route_name' => 'news-api','permission_name' => 'List','group' => 'news-api','is_default' => 0),
            array('module' => 'News API','route_name' => 'store-post','permission_name' => 'Add','group' => 'news-api','is_default' => 0),
            array('module' => 'Language','route_name' => 'language','permission_name' => 'List','group' => 'language','is_default' => 0),
            array('module' => 'Language','route_name' => 'add-language','permission_name' => 'Add','group' => 'language','is_default' => 0),
            array('module' => 'Language','route_name' => 'update-language','permission_name' => 'Update','group' => 'language','is_default' => 0),
            array('module' => 'Language','route_name' => 'update-language-status','permission_name' => 'Status Change','group' => 'language','is_default' => 0),
            array('module' => 'Language','route_name' => 'delete-language','permission_name' => 'Delete','group' => 'language','is_default' => 0),
            array('module' => 'Translation','route_name' => 'translation','permission_name' => 'List','group' => 'translation','is_default' => 0),
            array('module' => 'Translation','route_name' => 'add-translation','permission_name' => 'Add','group' => 'translation','is_default' => 0),
            array('module' => 'Translation','route_name' => 'update-translation','permission_name' => 'Update','group' => 'translation','is_default' => 0),
            array('module' => 'Send Push Notification','route_name' => 'push-notification','permission_name' => 'List','group' => 'send-push-notification','is_default' => 0),
            array('module' => 'Send Push Notification','route_name' => 'send-push-notification','permission_name' => 'Send Notification','group' => 'send-push-notification','is_default' => 0),
            array('module' => 'Send Push Notification','route_name' => 'delete-push-notification','permission_name' => 'Delete','group' => 'send-push-notification','is_default' => 0),
            array('module' => 'Search Blog','route_name' => 'search-log','permission_name' => 'List','group' => 'search-blog','is_default' => 0),
            array('module' => 'Profile','route_name' => 'profile','permission_name' => 'View','group' => 'profile','is_default' => 0),
            array('module' => 'Profile','route_name' => 'update-profile','permission_name' => 'Update','group' => 'profile','is_default' => 0),
            array('module' => 'Rss Feeds','route_name' => 'rss-feeds','permission_name' => 'List','group' => 'rss-feeds','is_default' => 0),
            array('module' => 'Rss Feeds','route_name' => 'add-rss-feeds','permission_name' => 'Add','group' => 'rss-feeds','is_default' => 0),
            array('module' => 'Rss Feeds','route_name' => 'update-rss-feeds','permission_name' => 'Update','group' => 'rss-feeds','is_default' => 0),
            array('module' => 'Rss Feeds','route_name' => 'update-rss-feeds-status','permission_name' => 'Status Change','group' => 'rss-feeds','is_default' => 0),
            array('module' => 'Rss Feeds','route_name' => 'delete-rss-feeds','permission_name' => 'Delete','group' => 'rss-feeds','is_default' => 0),
            array('module' => 'Rss Feed Items','route_name' => 'rss-feed-items','permission_name' => 'List','group' => 'rss-feed-items','is_default' => 0),
            array('module' => 'Rss Feed Items','route_name' => 'store-rss-item','permission_name' => 'Add','group' => 'rss-feed-items','is_default' => 0),
        );
         
        foreach ($tempArr as $key => $value) {
            Permission::insert([
                 'module' => $value['module'],
                 'name' => $value['route_name'],
                 'permission_name' => $value['permission_name'],
                 'group' => $value['group'],
                 'is_default' => $value['is_default'],
                 'created_at' => date("Y-m-d H:i:s")
            ]);
        }
    }
}
