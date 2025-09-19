<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;
use Validator;

use App\Models\CmsContent;
use App\Models\EPaper;
use App\Models\LiveNews;
use App\Models\Setting;
use App\Models\Language;
use App\Models\Visibility;
use App\Models\Translation;
use App\Models\SocialMediaLink;
use App\Models\Ad;

use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;


use Carbon\Carbon;


class SettingAPIController extends Controller
{
    private $language;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        $this->language = $request->header('language-code') && $request->header('language-code') != '' ? $request->header('language-code') : 'en';
    }
    /**
     *  Get list of Settings
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getSettings(Request $request)
    {
        try {
            // $cacheKey = 'settings:' . $request->fullUrl();
            // if (Cache::has($cacheKey)) {
            //     $cachedData = Cache::get($cacheKey);
            //     $cachedResponse = $cachedData['response'];
            //     $etag = $cachedData['ETag'];
            //     if ($request->header('If-None-Match') == $etag) {
            //         return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
            //     }
            //     return $cachedResponse;
            // }            
                        
            // $etagresponse = md5(json_encode($response->getData()));            
            // $response->setEtag($etagresponse);
            // $response->isNotModified($request);
            // $response->header('ETag', $etagresponse);            
            // $cachedDataArr = [
            //     'response' => $response,
            //     'ETag' => $etagresponse,
            // ];
            // Cache::put($cacheKey, $cachedDataArr, 60);
            // return $response;

            $finalData = array();
            $data = Setting::all();
            if(count($data)){
                foreach($data as $row){
                    $finalData[$row->key] = $row->value;
                }
            }
            $finalData['base_url'] = url('uploads/setting/');
            return $this->sendResponse($finalData, __('lang.message_data_retrived_successfully'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list  of Languages
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getLanguages(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $data = Language::where('status', 1)->get();
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));            
            $etagresponse = md5(json_encode($response->getData()));            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list of visibilities
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getVisibillities(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $data = Visibility::where('status', 1)->where('is_app',1)->get();
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));
            
            $etagresponse = md5(json_encode($response->getData()));
            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);
            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list of CMS
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getCms(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $data = CmsContent::all();
            if(count($data)){
                foreach($data as $row){
                    $row->image = url('uploads/cms/'.$row->image);
                }
            }
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));
            
            $etagresponse = md5(json_encode($response->getData()));
            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);
            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list of epaper
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getEpaper(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $data = EPaper::where('status', 1)->get();
            if(count($data)){
                foreach($data as $row){
                    $row->image = url('uploads/e-paper/'.$row->image);
                    $row->pdf = url('uploads/e-paper/pdf/'.$row->pdf);
                }
            }
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));
            
            $etagresponse = md5(json_encode($response->getData()));
            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);
            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list of live news
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getLiveNews(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $data = LiveNews::where('status', 1)->get();
            if(count($data)){
                foreach($data as $row){
                    $row->image = url('uploads/live-news/'.$row->image);
                }
            }
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));
            
            $etagresponse = md5(json_encode($response->getData()));
            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);
            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
    /**
     *  Get list of localisation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getLocalization(Request $request)
    {
        try {
            $cacheKey = 'settings:' . $request->fullUrl();
            if (Cache::has($cacheKey)) {
                $cachedData = Cache::get($cacheKey);
                $cachedResponse = $cachedData['response'];
                $etag = $cachedData['ETag'];
                if ($request->header('If-None-Match') == $etag) {
                    return response()->noContent()->setStatusCode(Response::HTTP_NOT_MODIFIED);
                }
                return $cachedResponse;
            }
            $finalData = array();
            $data = Translation::where('group','api')->where('language_id',$request->language_id)->get();
            if(count($data)){
                foreach($data as $row){
                    $finalData[$row->key] = $row->value;
                }
            }
            
            $response = $this->sendResponse($finalData,__('lang.message_data_retrived_successfully'));
            
            $etagresponse = md5(json_encode($response->getData()));
            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);
            
            $cachedDataArr = [
                'response' => $response,
                'ETag' => $etagresponse,
            ];
            Cache::put($cacheKey, $cachedDataArr, 60);
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     *  Get list of live news
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getSocialMedia(Request $request)
    {
        try {
            $data = SocialMediaLink::where('status',1)->get();
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));    
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     *  Get list of live news
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    function getAds(Request $request)
    {
        try {
            $data = Ad::where('status',1)->get();
            if(count($data)){
                foreach($data as $row){
                    if($row->media_type=='image'){
                        $row->media = url('uploads/ad/'.$row->media);
                    }else if($row->media_type=='video'){
                        $row->media = url('uploads/ad/video/'.$row->media);
                    }
                }
            }
            $response = $this->sendResponse($data, __('lang.message_data_retrived_successfully'));    
            return $response;
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
}
