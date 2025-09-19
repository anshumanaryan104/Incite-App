<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('login', 'App\Http\Controllers\API\UserAPIController@doLogin');
Route::post('signup', 'App\Http\Controllers\API\UserAPIController@doSignup');
Route::post('social-media-signup', 'App\Http\Controllers\API\UserAPIController@doSocialMediaSignup');
Route::post('forget-password', 'App\Http\Controllers\API\UserAPIController@doForgetPassword');
Route::post('reset-password', 'App\Http\Controllers\API\UserAPIController@doResetPassword');  
Route::match(['get', 'head'], 'setting-list', 'App\Http\Controllers\API\SettingAPIController@getSettings');
Route::match(['get', 'head'], 'language-list', 'App\Http\Controllers\API\SettingAPIController@getLanguages');
Route::match(['get', 'head'], 'visibility-list', 'App\Http\Controllers\API\SettingAPIController@getVisibillities');
Route::match(['get', 'head'], 'cms-list', 'App\Http\Controllers\API\SettingAPIController@getCms');
Route::match(['get', 'head'], 'epaper-list', 'App\Http\Controllers\API\SettingAPIController@getEpaper');
Route::match(['get', 'head'], 'live-news-list', 'App\Http\Controllers\API\SettingAPIController@getLiveNews');
Route::match(['get', 'head'], 'localisation-list', 'App\Http\Controllers\API\SettingAPIController@getLocalization');
Route::match(['get', 'head'], 'social-media-list', 'App\Http\Controllers\API\SettingAPIController@getSocialMedia');
Route::match(['get', 'head'], 'ads-list', 'App\Http\Controllers\API\SettingAPIController@getAds');
// Route::get('blog-list', 'App\Http\Controllers\API\BlogAPIController@list');  
// Route::get('blog-detail/{id}', 'App\Http\Controllers\API\BlogAPIController@detail');  
Route::match(['get', 'head'],'blog-list', 'App\Http\Controllers\API\BlogAPIController@getList');  
Route::match(['get', 'head'],'blog-detail/{id}', 'App\Http\Controllers\API\BlogAPIController@getDetail');  
Route::post('update-token', 'App\Http\Controllers\API\UserAPIController@doUpdateToken');
Route::post('get-notification-detail', 'App\Http\Controllers\API\UserAPIController@doGetNotificationDetail');
Route::post('add-analytics', 'App\Http\Controllers\API\BlogAPIController@addAnalytics');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('apiauth:api')->group(function () {
    // Route::get('get-profile', 'App\Http\Controllers\API\UserAPIController@getProfile');
    Route::match(['get', 'head'],'get-profile', 'App\Http\Controllers\API\UserAPIController@getProfile');
    Route::post('update-profile', 'App\Http\Controllers\API\UserAPIController@doUpdateProfile');
    Route::post('change-password', 'App\Http\Controllers\API\UserAPIController@doChangePassword');
    Route::get('delete-account', 'App\Http\Controllers\API\UserAPIController@doDeleteAccount');    
    Route::post('blog-search', 'App\Http\Controllers\API\BlogAPIController@search');
    Route::post('add-feed', 'App\Http\Controllers\API\CategoryAPIController@doAddFeed');
    Route::post('add-vote', 'App\Http\Controllers\API\BlogAPIController@doVoteForOption');
    Route::get('get-bookmarks', 'App\Http\Controllers\API\BlogAPIController@doGetBookmarks');    

});
