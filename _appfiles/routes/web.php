<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/clear', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return "Cache is cleared";
});
Route::middleware('admin-language:web')->group(function () {
    Route::get('/admin-login','App\Http\Controllers\Auth\LoginController@getLoginView')->middleware(['check.app.installation','check.app.code_verified']);
    Route::get('/admin-forget-password','App\Http\Controllers\Auth\ForgotPasswordController@forgetPassword')->middleware(['check.app.installation','check.app.code_verified']);
    Route::get('/admin-reset-password','App\Http\Controllers\Auth\ResetPasswordController@resetPassword')->middleware(['check.app.installation','check.app.code_verified']);
    Route::post('do-login', 'App\Http\Controllers\Auth\LoginController@authenticate');
    Route::post('do-admin-forget-password', 'App\Http\Controllers\Auth\ForgotPasswordController@forgetPasswordPost');
    Route::post('do-admin-reset-password', 'App\Http\Controllers\Auth\ResetPasswordController@resetPasswordPost');
    Route::get('logout', 'App\Http\Controllers\Auth\LoginController@logout');
    Route::get('/update-website','App\Http\Controllers\Admin\DashboardController@updateWebsite');

    Route::get('/', function () {
        return view('welcome');
    })->middleware(['check.app.installation','check.app.code_verified']);
    Route::get('/licenses-verify', 'App\Http\Controllers\LicenseController@index')->middleware('check.app.installation');
    Route::post('/licenses-verify', 'App\Http\Controllers\LicenseController@verify')->name('license.verify')->middleware('check.app.installation');
    Route::middleware(['permission','check.app.installation','check.app.code_verified'])->group(function () {  
        Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'checkSubadminStatus']], function () {

            Route::get('/dashboard','App\Http\Controllers\Admin\DashboardController@index');
            Route::get('/','App\Http\Controllers\Admin\DashboardController@index');
            Route::get('/profile', 'App\Http\Controllers\Admin\UserController@profile');
            Route::post('/update-profile', 'App\Http\Controllers\Admin\UserController@updateProfile');

            /************************* Category Routing Starts Here **************************/

            Route::get('/category', 'App\Http\Controllers\Admin\CategoryController@index');
            Route::post('/category', 'App\Http\Controllers\Admin\CategoryController@index');
            Route::post('/add-category', 'App\Http\Controllers\Admin\CategoryController@store');
            Route::post('/update-category', 'App\Http\Controllers\Admin\CategoryController@update');
            Route::delete('/delete-category/{id}', 'App\Http\Controllers\Admin\CategoryController@destroy');
            Route::get('/update-category-column/{id}/{name}/{value}', 'App\Http\Controllers\Admin\CategoryController@updateColumn');
            Route::get('/translation-category/{id}', 'App\Http\Controllers\Admin\CategoryController@translation');
            Route::post('/translation-category/{id}', 'App\Http\Controllers\Admin\CategoryController@updateTranslation');
            Route::post('/translation-by-third-party', 'App\Http\Controllers\Admin\CategoryController@translateByThirdParty');
            Route::post('/translation-by-google', 'App\Http\Controllers\Admin\CategoryController@translateByGoogle');

            /************************* Category Routing Starts Here **************************/

            /************************* Quotes Routing Starts Here **************************/

            Route::get('/quotes', 'App\Http\Controllers\Admin\QuoteController@index');
            Route::post('/quotes', 'App\Http\Controllers\Admin\QuoteController@index');
            Route::post('/add-quote', 'App\Http\Controllers\Admin\QuoteController@store');
            Route::post('/update-quote', 'App\Http\Controllers\Admin\QuoteController@update');
            Route::delete('/delete-quote/{id}', 'App\Http\Controllers\Admin\QuoteController@destroy');
            Route::get('/update-quote-column/{id}/{name}/{value}', 'App\Http\Controllers\Admin\QuoteController@updateColumn');
            Route::get('/quote/translation/{id}', 'App\Http\Controllers\Admin\QuoteController@translation');
            Route::post('/quote/translation/{id}', 'App\Http\Controllers\Admin\QuoteController@updateTranslation');

            /************************* Quotes Routing Starts Here **************************/

            /************************* Live News Routing Starts Here **************************/

            Route::get('/live-news', 'App\Http\Controllers\Admin\LiveNewsController@index');
            Route::post('/live-news', 'App\Http\Controllers\Admin\LiveNewsController@index');
            Route::post('/add-live-news', 'App\Http\Controllers\Admin\LiveNewsController@store');
            Route::post('/update-live-news', 'App\Http\Controllers\Admin\LiveNewsController@update');
            Route::delete('/delete-live-news/{id}', 'App\Http\Controllers\Admin\LiveNewsController@destroy');
            Route::get('/detail-live-news/{id}', 'App\Http\Controllers\Admin\LiveNewsController@show');
            Route::get('/update-live-news-status/{id}/{value}', 'App\Http\Controllers\Admin\LiveNewsController@changeStatus');
            Route::get('/translation-live-news/{id}', 'App\Http\Controllers\Admin\LiveNewsController@translation');
            Route::post('/translation-live-news/{id}', 'App\Http\Controllers\Admin\LiveNewsController@updateTranslation');

            /************************* Live News Routing Starts Here **************************/

            /************************* E-Paper Routing Starts Here **************************/

            Route::get('/e-papers', 'App\Http\Controllers\Admin\EpaperController@index');
            Route::post('/e-papers', 'App\Http\Controllers\Admin\EpaperController@index');
            Route::post('/add-e-paper', 'App\Http\Controllers\Admin\EpaperController@store');
            Route::post('/update-e-paper', 'App\Http\Controllers\Admin\EpaperController@update');
            Route::delete('/delete-e-paper/{id}', 'App\Http\Controllers\Admin\EpaperController@destroy');
            Route::get('/detail-e-paper/{id}', 'App\Http\Controllers\Admin\EpaperController@show');
            Route::get('/update-e-paper-status/{id}/{value}', 'App\Http\Controllers\Admin\EpaperController@changeStatus');
            Route::get('/translation-e-paper/{id}', 'App\Http\Controllers\Admin\EpaperController@translation');
            Route::post('/translation-e-paper/{id}', 'App\Http\Controllers\Admin\EpaperController@updateTranslation');

            /************************* E-Paper Routing Starts Here **************************/

            /************************* CMS Routing Starts Here **************************/

            Route::get('/cms', 'App\Http\Controllers\Admin\CmsController@index');
            Route::post('/cms', 'App\Http\Controllers\Admin\CmsController@index');
            Route::get('/add-cms', 'App\Http\Controllers\Admin\CmsController@create');
            Route::post('/add-cms', 'App\Http\Controllers\Admin\CmsController@store');
            Route::get('/update-cms/{id}', 'App\Http\Controllers\Admin\CmsController@edit');
            Route::post('/update-cms', 'App\Http\Controllers\Admin\CmsController@update');
            Route::delete('/delete-cms/{id}', 'App\Http\Controllers\Admin\CmsController@destroy');
            Route::get('/detail-cms/{id}', 'App\Http\Controllers\Admin\CmsController@show');
            Route::get('/update-cms-status/{id}/{value}', 'App\Http\Controllers\Admin\CmsController@changeStatus');
            Route::get('/translation-cms/{id}', 'App\Http\Controllers\Admin\CmsController@translation');
            Route::post('/translation-cms/{id}', 'App\Http\Controllers\Admin\CmsController@updateTranslation');

            /************************* CMS Routing Starts Here **************************/

            /************************* Settings Routing Starts Here **************************/

            Route::get('/settings/{type}', 'App\Http\Controllers\Admin\SettingController@index');
            Route::post('/update-setting', 'App\Http\Controllers\Admin\SettingController@update');
            Route::get('/setlang', 'App\Http\Controllers\Admin\SettingController@setLanguage');

            /************************* Settings Routing Starts Here **************************/

            /************************* Social Media Settings Routing Starts Here **************************/

            Route::get('/social-media', 'App\Http\Controllers\Admin\SocialMediaLinkController@index');
            Route::post('/social-media', 'App\Http\Controllers\Admin\SocialMediaLinkController@index');
            Route::post('/add-social-media', 'App\Http\Controllers\Admin\SocialMediaLinkController@store');
            Route::post('/update-social-media', 'App\Http\Controllers\Admin\SocialMediaLinkController@update');
            Route::delete('/delete-social-media/{id}', 'App\Http\Controllers\Admin\SocialMediaLinkController@destroy');
            Route::get('/update-social-media-status/{id}/{value}', 'App\Http\Controllers\Admin\SocialMediaLinkController@updateColumn');

            /************************* Social Media Settings Starts Here **************************/

            /************************* User Settings Routing Starts Here **************************/

            Route::get('/user', 'App\Http\Controllers\Admin\UserController@index');
            Route::post('/user', 'App\Http\Controllers\Admin\UserController@index');
            // Route::post('/add-social-media', 'App\Http\Controllers\Admin\UserController@store');
            // Route::post('/update-social-media', 'App\Http\Controllers\Admin\UserController@update');
            Route::delete('/delete-user/{id}', 'App\Http\Controllers\Admin\UserController@destroy');
            Route::get('/update-user-status/{id}/{value}', 'App\Http\Controllers\Admin\UserController@updateColumn');
            Route::get('/personlization/{id}', 'App\Http\Controllers\Admin\UserController@personalization');


            /************************* User Settings Starts Here **************************/

            /************************* Social Media Settings Routing Starts Here **************************/

            Route::get('/sub-admin', 'App\Http\Controllers\Admin\SubAdminController@index');
            Route::post('/sub-admin', 'App\Http\Controllers\Admin\SubAdminController@index');
            Route::post('/add-sub-admin', 'App\Http\Controllers\Admin\SubAdminController@store');
            Route::post('/update-sub-admin', 'App\Http\Controllers\Admin\SubAdminController@update');
            Route::delete('/delete-sub-admin/{id}', 'App\Http\Controllers\Admin\SubAdminController@destroy');
            Route::get('/update-sub-admin-status/{id}/{value}', 'App\Http\Controllers\Admin\SubAdminController@updateColumn');

            /************************* Social Media Settings Starts Here **************************/

            /************************* Role Routing Starts Here **************************/

            Route::get('/role', 'App\Http\Controllers\Admin\RoleController@index');
            Route::post('/role', 'App\Http\Controllers\Admin\RoleController@index');
            Route::post('/add-role', 'App\Http\Controllers\Admin\RoleController@store');
            Route::post('/update-role', 'App\Http\Controllers\Admin\RoleController@update');
            Route::delete('/delete-role/{id}', 'App\Http\Controllers\Admin\RoleController@destroy');
            Route::get('/update-role-status/{id}/{value}', 'App\Http\Controllers\Admin\RoleController@updateColumn');

            /************************* Role Starts Here **************************/

            /************************* Blog Routing Starts Here **************************/

            Route::get('/blog', 'App\Http\Controllers\Admin\BlogController@index');
            Route::post('/blog', 'App\Http\Controllers\Admin\BlogController@index');
            Route::get('/add-blog/{type}', 'App\Http\Controllers\Admin\BlogController@create');

            // Route::get('/blog/edit-blog/{id}', 'App\Http\Controllers\Admin\BlogController@edit');
            Route::get('/update-blog/{type}/{id}', 'App\Http\Controllers\Admin\BlogController@edit');

            Route::post('/add-blog', 'App\Http\Controllers\Admin\BlogController@store');
            Route::post('/add-quote', 'App\Http\Controllers\Admin\BlogController@storeQuote');
            Route::post('/add-ad', 'App\Http\Controllers\Admin\BlogController@storeAd');
            Route::post('/update-blog', 'App\Http\Controllers\Admin\BlogController@update');
            Route::post('/publish-blog', 'App\Http\Controllers\Admin\BlogController@update');
            Route::post('/update-quote', 'App\Http\Controllers\Admin\BlogController@updateQuote');
            Route::post('/update-ad', 'App\Http\Controllers\Admin\BlogController@updateAd');
            Route::delete('/delete-blog/{id}', 'App\Http\Controllers\Admin\BlogController@destroy');
            Route::get('/update-blog-status/{id}/{value}', 'App\Http\Controllers\Admin\BlogController@changeStatus');
            Route::get('/blog/translation/{id}', 'App\Http\Controllers\Admin\BlogController@translation');
            Route::post('/blog/translation/{id}', 'App\Http\Controllers\Admin\BlogController@updateTranslation');
            Route::post('/get-subcategory', 'App\Http\Controllers\Admin\BlogController@getSubcategories');
            Route::post('/store-image', 'App\Http\Controllers\Admin\BlogController@storeImage');
            Route::post('/remove-image', 'App\Http\Controllers\Admin\BlogController@removeImage');
            Route::post('/remove-image-by-name', 'App\Http\Controllers\Admin\BlogController@removeImageByName');
            Route::post('/blog-image-sortable','App\Http\Controllers\Admin\BlogController@sorting');
            Route::get('/send-notification/{id}', 'App\Http\Controllers\Admin\BlogController@sendNotification');
            Route::get('/analytics/{id}', 'App\Http\Controllers\Admin\BlogController@analytics');

            /************************* Blog Starts Here **************************/

            /************************* Visibility Routing Starts Here **************************/

            Route::get('/visibility', 'App\Http\Controllers\Admin\VisibilityController@index');
            Route::post('/visibility', 'App\Http\Controllers\Admin\VisibilityController@index');
            Route::post('/add-visibility', 'App\Http\Controllers\Admin\VisibilityController@store');
            Route::post('/update-visibility', 'App\Http\Controllers\Admin\VisibilityController@update');
            Route::delete('/delete-visibility/{id}', 'App\Http\Controllers\Admin\VisibilityController@destroy');
            Route::get('/update-visibility-status/{id}/{value}', 'App\Http\Controllers\Admin\VisibilityController@changeStatus');
            Route::get('/translation-visibility/{id}', 'App\Http\Controllers\Admin\VisibilityController@translation');
            Route::post('/translation-visibility/{id}', 'App\Http\Controllers\Admin\VisibilityController@updateTranslation');

            /************************* Visibility Routing Starts Here **************************/

            /************************* Ads Routing Starts Here **************************/

            Route::get('/ads', 'App\Http\Controllers\Admin\AdController@index');
            Route::post('/ads', 'App\Http\Controllers\Admin\AdController@index');
            Route::get('/add-ad', 'App\Http\Controllers\Admin\AdController@create');
            Route::post('/add-ad', 'App\Http\Controllers\Admin\AdController@store');
            Route::get('/update-ad/{id}', 'App\Http\Controllers\Admin\AdController@edit');
            Route::post('/update-ad', 'App\Http\Controllers\Admin\AdController@update');
            Route::delete('/delete-ad/{id}', 'App\Http\Controllers\Admin\AdController@destroy');
            Route::get('/detail-ad/{id}', 'App\Http\Controllers\Admin\AdController@show');
            Route::get('/update-ad-status/{id}/{value}', 'App\Http\Controllers\Admin\AdController@changeStatus');
            Route::post('/ads-sortable','App\Http\Controllers\Admin\AdController@sorting');

            /************************* Ads Routing Starts Here **************************/

            /************************* Ads Routing Starts Here **************************/

            Route::get('/news-api', 'App\Http\Controllers\Admin\NewsApiController@index');
            Route::post('/news-api', 'App\Http\Controllers\Admin\NewsApiController@index');
            Route::post('/store-post', 'App\Http\Controllers\Admin\NewsApiController@store');

            /************************* Ads Routing Starts Here **************************/

            /************************* Languages Routing Starts Here **************************/

            Route::get('/languages', 'App\Http\Controllers\Admin\LanguageController@index');
            Route::post('/languages', 'App\Http\Controllers\Admin\LanguageController@index');
            Route::post('/add-language', 'App\Http\Controllers\Admin\LanguageController@store');
            Route::post('/update-language', 'App\Http\Controllers\Admin\LanguageController@update');
            Route::delete('/delete-language/{id}', 'App\Http\Controllers\Admin\LanguageController@destroy');
            Route::get('/update-language-status/{id}/{value}', 'App\Http\Controllers\Admin\LanguageController@changeStatus');

            /************************* Languages Routing Starts Here **************************/

            /************************* Languages Routing Starts Here **************************/

            Route::get('/translation', 'App\Http\Controllers\Admin\TranslationController@index');
            Route::post('/translation', 'App\Http\Controllers\Admin\TranslationController@index');
            Route::post('/add-translation', 'App\Http\Controllers\Admin\TranslationController@store');
            Route::get('/update-translation/{id}', 'App\Http\Controllers\Admin\TranslationController@edit');
            Route::post('/update-translation', 'App\Http\Controllers\Admin\TranslationController@update');
            // Route::delete('/delete-translation/{id}', 'App\Http\Controllers\Admin\LanguageController@destroy');
            // Route::get('/update-translation-status/{id}/{value}', 'App\Http\Controllers\Admin\LanguageController@changeStatus');

            /************************* Languages Routing Starts Here **************************/

            /************************* Push Notification Routing Starts Here **************************/

            Route::get('/push-notification', 'App\Http\Controllers\Admin\PushNotificationController@index');
            Route::post('/push-notification', 'App\Http\Controllers\Admin\PushNotificationController@index');
            Route::get('/send-push-notification', 'App\Http\Controllers\Admin\PushNotificationController@create');
            Route::post('/send-push-notification', 'App\Http\Controllers\Admin\PushNotificationController@store');

            /************************* Push Notification Routings Starts Here **************************/

            /************************* Push Notification Routing Starts Here **************************/

            Route::get('/search-log', 'App\Http\Controllers\Admin\SearchLogController@index');
            Route::post('/search-log', 'App\Http\Controllers\Admin\SearchLogController@index');

            /************************* Push Notification Routings Starts Here **************************/

            /************************* Rss Feeds Routing Starts Here **************************/

            Route::get('/rss-feeds', 'App\Http\Controllers\Admin\RssFeedController@index');
            Route::post('/rss-feeds', 'App\Http\Controllers\Admin\RssFeedController@index');
            Route::post('/add-rss-feeds', 'App\Http\Controllers\Admin\RssFeedController@store');
            Route::post('/update-rss-feeds', 'App\Http\Controllers\Admin\RssFeedController@update');
            Route::delete('/delete-rss-feeds/{id}', 'App\Http\Controllers\Admin\RssFeedController@destroy');
            Route::get('/update-rss-feeds-status/{id}/{value}', 'App\Http\Controllers\Admin\RssFeedController@updateColumn');  
            Route::get('/check-rss/{id}', 'App\Http\Controllers\Admin\RssFeedController@checkFeedItems');          

            /************************* Rss Feeds Routing Ends Here **************************/

            Route::get('/rss-feed-items', 'App\Http\Controllers\Admin\RssFeedItemController@index');
            Route::post('/rss-feed-items', 'App\Http\Controllers\Admin\RssFeedItemController@index');
            Route::post('/store-rss-item', 'App\Http\Controllers\Admin\RssFeedItemController@store');
            Route::post('/get-source', 'App\Http\Controllers\Admin\RssFeedItemController@getSource');
        });



        Auth::routes();

        
    });
});
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/unauthorized', function () {
    return view('unauthorized');
})->name('unauthorized');
