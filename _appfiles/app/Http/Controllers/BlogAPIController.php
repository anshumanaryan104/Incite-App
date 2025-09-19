<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;
use Validator;

use App\Models\Blog;
use App\Models\Category;
use App\Models\BlogCategory;
use App\Models\SearchLog;
use App\Models\Vote;
use App\Models\BlogTranslation;
use App\Models\Ad;
use App\Models\User;
use App\Models\BlogAnalytic;
use App\Models\BlogBookmark;


use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class BlogAPIController extends Controller
{
    private $language;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        $this->language = $request->header('language-code') && $request->header('language-code') != '' ? $request->header('language-code') : 'en';
    }

    function getList(Request $request)
    {
        // $response = $client->request('POST', 'https://onesignal.com/api/v1/notifications', [
        //     'body' => '{"included_segments":["Subscribed Users"],"contents":{"en":"English or Any Language Message","es":"Spanish Message"},"name":"INTERNAL_CAMPAIGN_NAME"}',
        //     'headers' => [
        //       'Authorization' => 'Basic YOUR_REST_API_KEY',
        //       'accept' => 'application/json',
        //       'content-type' => 'application/json',
        //     ],
        // ]);
          
        // echo $response->getBody();
        try{
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
            
            $pagination_no = config('constant.api_pagination');
            if(isset($search['per_page']) && !empty($search['per_page'])){
                $pagination_no = $search['per_page'];
            }
            $categories = Category::select('id','parent_id','name','image','color','is_featured','created_at','updated_at','deleted_at')->where('status',1)->get();
            if(count($categories)){
                foreach($categories as $category_data){
                    $category_data->image = url('uploads/category/'.$category_data->image);
                }
            }
            $ads_list = Ad::select('id','title','media_type','media','image_url','source_name','source_link','frequency','created_at','updated_at','deleted_at')->where('status',1)->where('end_date',">=",date('Y-m-d'))->orderBy('order','ASC')->get();
            if(count($ads_list)){
                foreach($ads_list as $ads_data){
                    if($ads_data->media_type=='image'){
                        $ads_data->media = url('uploads/ad/'.$ads_data->media);
                    }else if($ads_data->media_type=='video'){
                        $ads_data->media = url('uploads/ad/video/'.$ads_data->media);
                    }
                }
            }
            if(count($categories)){
                foreach($categories as $row){
                    $row->is_feed = false;
                    $row->api_token = $request->header('api-token');
                    if($request->header('api-token')!=''){
                        $user = User::where('api_token',$request->header('api-token'))->first();
                        if($user){
                            $row->is_feed = \Helpers::categoryIsInFeed($row->id,$user->id);
                        }                
                    }
                    $blog_arr = array();
                    $blog_arr = \Helpers::getBlogsArrOnTheBasisOfCategory($row->id);
                    $blogs = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where('status',1)->whereIn('id',$blog_arr)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->with('blog_sub_category')->orderBy('schedule_date','DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
                    if(count($blogs)){
                        foreach($blogs as $blog){
                            $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                            if ($blogTranslate) {
                                $blog->title = $blogTranslate->title;
                                $blog->description = $blogTranslate->description;
                            }
                            $blog->is_feed = false;
                            $blog->is_vote = 0;
                            $blog->is_bookmark = 0;
                            $blog->is_user_viewed = 0; 
                            if($request->header('api-token')!=''){
                                $user = User::where('api_token',$request->header('api-token'))->first();
                                if($user){
                                    $blog->is_feed = \Helpers::categoryIsInFeed($row->id,$user->id);
                                    $blog->is_vote = \Helpers::getVotes($blog->id,$user->id);              
                                    $blog->is_bookmark = \Helpers::getBookmarks($blog->id,$user->id);              
                                    $blog->is_user_viewed = \Helpers::getViewed($blog->id,$user->id);              
                                }  
                            } 
                                                       
                            $blog->visibilities = \Helpers::getVisibilities($blog->id);
                            $blog->question = \Helpers::getQuestionsOptions($blog->id);
                            $blog->images = \Helpers::getBlogImages($blog->id,'768x428');
                            if($blog->background_image!=''){
                                $blog->background_image = url('uploads/blog/'.$blog->background_image);
                            }
                            if(count($blog->blog_sub_category)){
                                foreach($blog->blog_sub_category as $blog_sub_category){
                                    if($blog_sub_category->category!=''){
                                        if($blog_sub_category->category->image!=''){
                                            $blog_sub_category->category->image = url('uploads/category/'.$blog_sub_category->category->image);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    $result = [];
                    $adCount = count($ads_list);
                    $adIndex = 0;

                    $BlogCount = 0;
                    if(count($ads_list)){
                        foreach ($blogs as $key => $item) {
                            if ($BlogCount == $ads_list[$adIndex]['frequency']) {
                                $ads_list[$adIndex]['type'] = "ads";
                                $result[] = $ads_list[$adIndex];
                                $adIndex = ($adIndex + 1) % $adCount;
                                $BlogCount = 0;
                            }
                            $result[] = $item;                
                            $BlogCount++;
                        }
                    }else{
                        foreach ($blogs as $key => $item) {
                            $result[] = $item;
                        }
                    }
                    $row->blogs = \Helpers::arrayPaginator($result,$request);
                }
            }
            $response = $this->sendResponse($categories, __('lang.message_data_retrived_successfully'));            
            $etagresponse = md5(json_encode($response->getData()));            
            $response->setEtag($etagresponse);
            $response->isNotModified($request);
            $response->header('ETag', $etagresponse);            
            $response->header('api-token', $request->header('api-token'));            
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

    function getDetail(Request $request,$id)
    {
        try{
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
            $blog = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where('status',1)->with('images')->with('blog_visibility')->with('blog_category')->with('blog_sub_category')->first();
            if($blog){
                $blog->images = \Helpers::getBlogImages($blog->images,'768x428');
            }
            $response = $this->sendResponse($blog, __('lang.message_data_retrived_successfully'));            
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

    function search(Request $request)
    {
        try{
            if($request->userAuthData){
                $blogs = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where(function($query) use ($request) {
                    $query->where('title', 'like', '%' . trim($request->input('keyword')) . '%')
                        ->orWhere('tags', 'like', '%' . trim($request->input('keyword')) . '%');
                    })
                ->where('status', 1)
                ->where('schedule_date', '<=', date("Y-m-d H:i:s"))
                ->with('blog_category')
                ->get(); 
                if(count($blogs)){
                    foreach($blogs as $blog){
                        $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                        if ($blogTranslate) {
                            $blog->title = $blogTranslate->title;
                            $blog->description = $blogTranslate->description;
                        }
                        $blog->is_feed = false;
                        $blog->is_vote = 0;
                        $blog->is_bookmark = 0;
                        $blog->is_user_viewed = 0; 
                        if($request->header('api-token')!=''){
                            $user = User::where('api_token',$request->header('api-token'))->first();
                            if($user){
                                $blog->is_feed = \Helpers::categoryIsInFeed($row->id,$user->id);
                                $blog->is_vote = \Helpers::getVotes($blog->id,$user->id);              
                                $blog->is_bookmark = \Helpers::getBookmarks($blog->id,$user->id);              
                                $blog->is_user_viewed = \Helpers::getViewed($blog->id,$user->id);              
                            }  
                        } 
                                                   
                        $blog->visibilities = \Helpers::getVisibilities($blog->id);
                        $blog->question = \Helpers::getQuestionsOptions($blog->id);
                        $blog->images = \Helpers::getBlogImages($blog->id,'768x428');
                        if($blog->background_image!=''){
                            $blog->background_image = url('uploads/blog/'.$blog->background_image);
                        }
                        if(count($blog->blog_sub_category)){
                            foreach($blog->blog_sub_category as $blog_sub_category){
                                if($blog_sub_category->category!=''){
                                    if($blog_sub_category->category->image!=''){
                                        $blog_sub_category->category->image = url('uploads/category/'.$blog_sub_category->category->image);
                                    }
                                }
                            }
                        }
                    }
                }
                $search = array(
                    'user_id'=>$request->userAuthData->id,
                    'keyword'=>$request->input('keyword'),
                    'count'=>count($blogs),
                    'created_at'=>date('Y-m-d h:i:s')
                );
                SearchLog::insert($search);
                return $this->sendResponse($blogs,__('lang.message_data_retrived_successfully'));
            }   
            return $this->sendError(__('lang.message_user_not_found'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     *  Add user feed
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doVoteForOption(Request $request)
    {
        try{
            if($request->userAuthData){
                $validate = [
                    'blog_id' => 'required',
                    'option_id' => 'required'
                ];
                $validator = Validator::make($request->all(), $validate);
                if ($validator->fails()) {
                    return $this->sendError(__('lang.message_required_message'),$validator->errors());
                }                
                $checkVote = Vote::where('blog_id', $request->input('blog_id'))->where('user_id',$request->userAuthData->id)->first();
                if ($checkVote) {
                    return $this->sendError(__('lang.message_vote_already_exist'));
                }
                $postData = array(
                    'blog_id'=>$request->input('blog_id'),
                    'option_id'=>$request->input('option_id'),
                    'user_id'=>$request->userAuthData->id,
                    'created_at' => date("Y-m-d H:i:s")
                );
                Vote::insert($postData);
                $data = \Helpers::getQuestionsOptions($request->input('blog_id'));
                return $this->sendResponse($data, __('lang.message_vote_added_successfully'));
            }
            return $this->sendError(__('lang.message_user_not_found'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    public function addAnalytics(Request $request)
    {
        try{
            $post = $request->all();
            $user_id = 0;
            if($request->header('api-token')!=''){
                $user = User::where('api_token',$request->header('api-token'))->first();
                if($user){
                    $user_id = $user->id;
                }                
            } 
            if(isset($post) && !empty($post)){
                foreach($post as $post_data){
                    if($post_data['type']=='bookmark'){
                        for($i=0;$i<count($post_data['blog_ids']);$i++){
                            $checkBookmark = BlogBookmark::where('user_id',$user_id)->where('blog_id',$post_data['blog_ids'][$i])->first();
                            if(!$checkBookmark){
                                $analyticsArr = array(
                                    'user_id'=>$user_id,
                                    'blog_id'=>$post_data['blog_ids'][$i],
                                    'created_at'=>date('Y-m-d H:i:s'),
                                );
                                BlogBookmark::insert($analyticsArr);
                            }                            
                        }                        
                    }else if($post_data['type']=='view' || $post_data['type']=='share' || $post_data['type']=='poll_share'){
                        for($i=0;$i<count($post_data['blog_ids']);$i++){
                            $checkAnalytics = BlogAnalytic::where('type',$post_data['type'])->where('user_id',$user_id)->where('blog_id',$post_data['blog_ids'][$i])->first();
                            if(!$checkAnalytics){
                                $analyticsArr = array(
                                    'type'=>$post_data['type'],
                                    'user_id'=>$user_id,
                                    'blog_id' => $post_data['blog_ids'][$i],
                                    'created_at'=>date('Y-m-d H:i:s'),
                                );                                
                                BlogAnalytic::insert($analyticsArr);
                            }
                        }    
                    }else if($post_data['type']=='blog_time_spent' || $post_data['type']=='tts'){
                        if(isset($post_data['blogs']) && count($post_data['blogs'])){
                            foreach($post_data['blogs'] as $blog_time_spent){
                                $analyticsArr = array(
                                    'type'=>$post_data['type'],
                                    'user_id'=>$user_id,
                                    'blog_id'=>$blog_time_spent['id'],
                                    'start_date_time'=>date("Y-m-d H:i:s",strtotime($blog_time_spent['start_time'])),
                                    'end_date_time'=>date("Y-m-d H:i:s",strtotime($blog_time_spent['end_time'])),
                                    'created_at'=>date('Y-m-d H:i:s'),
                                );
                                BlogAnalytic::insert($analyticsArr);                                
                            }                            
                        }
                    }else if($post_data['type']=='app_time_spent'){
                        $analyticsArr = array(
                            'type'=>$post_data['type'],
                            'user_id'=>$user_id,
                            'start_date_time'=>date("Y-m-d H:i:s",strtotime($post_data['start_time'])),
                            'end_date_time'=>date("Y-m-d H:i:s",strtotime($post_data['end_time'])),
                            'created_at'=>date('Y-m-d H:i:s'),
                        );
                        BlogAnalytic::insert($analyticsArr);
                    }else if($post_data['type']=='social_media_signin' || $post_data['type']=='sign_in' || $post_data['type']=='social_media_signup' || $post_data['type']=='sign_up'){
                        $checkAnalytics = BlogAnalytic::where('type',$post_data['type'])->where('user_id',$user_id)->first();
                        if(!$checkAnalytics){
                            $analyticsArr = array(
                                'type'=>$post_data['type'],
                                'user_id'=>$user_id,
                                'created_at'=>date('Y-m-d H:i:s'),
                            );
                            if(isset($post_data['start_time']) && $post_data['start_time']!=''){
                                $analyticsArr['start_date_time'] = date("Y-m-d H:i:s",strtotime($post_data['start_time']));
                            }
                            if(isset($post_data['end_time']) && $post_data['end_time']!=''){
                                $analyticsArr['end_date_time'] = date("Y-m-d H:i:s",strtotime($post_data['end_time']));
                            }
                            if(isset($post_data['action']) && $post_data['action']!=''){
                                $analyticsArr['action'] = $post_data['action'];
                            }
                            BlogAnalytic::insert($analyticsArr);
                        }else{
                            $analyticsArr = array(
                                'updated_at'=>date('Y-m-d H:i:s'),
                            );
                            if(isset($post_data['start_time']) && $post_data['start_time']!=''){
                                $analyticsArr['start_date_time'] = date("Y-m-d H:i:s",strtotime($post_data['start_time']));
                            }
                            if(isset($post_data['end_time']) && $post_data['end_time']!=''){
                                $analyticsArr['end_date_time'] = date("Y-m-d H:i:s",strtotime($post_data['end_time']));
                            }
                            if(isset($post_data['action']) && $post_data['action']!=''){
                                $analyticsArr['action'] = $post_data['action'];
                            }
                            BlogAnalytic::where('id',$checkAnalytics->id)->update($analyticsArr);
                        }
                    }else if($post_data['type']=='remove_bookmark'){
                        for($i=0;$i<count($post_data['blog_ids']);$i++){
                            $checkBookmark = BlogBookmark::where('user_id',$user_id)->where('blog_id',$post_data['blog_ids'][$i])->first();
                            if($checkBookmark){
                                BlogBookmark::where('user_id',$user_id)->where('blog_id',$post_data['blog_ids'][$i])->delete();
                            }                            
                        } 
                    }
                }
            }
            return $this->sendResponse([],__('lang.message_data_retrived_successfully'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    function doGetBookmarks(Request $request)
    {
        try{
            if($request->userAuthData){
                $pagination_no = config('constant.api_pagination');
                if(isset($search['per_page']) && !empty($search['per_page'])){
                    $pagination_no = $search['per_page'];
                }
                $blog_id_arr = array();
                $bookmarks = BlogBookmark::where('user_id',$request->userAuthData->id)->get();
                if(count($bookmarks)){
                    foreach($bookmarks as $bookmarks_data){
                        if(!in_array($bookmarks_data->blog_id,$blog_id_arr)){
                            array_push($blog_id_arr,$bookmarks_data->blog_id);
                        }
                    }
                }
                $blogs = array();
                if(count($blog_id_arr)){
                    $blogs = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where('status',1)->whereIn('id',$blog_id_arr)->where('schedule_date',"<=",date("Y-m-d H:i:s"))->with('blog_sub_category')->orderBy('schedule_date','DESC')->paginate($pagination_no)->appends('per_page', $pagination_no);
                    if(count($blogs)){
                        foreach($blogs as $blog){
                            $blogTranslate = BlogTranslation::where('blog_id',$blog->id)->where('language_code',$this->language)->first();
                            if ($blogTranslate) {
                                $blog->title = $blogTranslate->title;
                                $blog->description = $blogTranslate->description;
                            }
                            $blog->is_feed = false;
                            $blog->is_vote = 0;
                            $blog->is_bookmark = 0;
                            $blog->is_user_viewed = 0; 
                            if($request->header('api-token')!=''){
                                $user = User::where('api_token',$request->header('api-token'))->first();
                                if($user){
                                    $blog->is_feed = \Helpers::categoryIsInFeed($row->id,$user->id);
                                    $blog->is_vote = \Helpers::getVotes($blog->id,$user->id);              
                                    $blog->is_bookmark = \Helpers::getBookmarks($blog->id,$user->id);              
                                    $blog->is_user_viewed = \Helpers::getViewed($blog->id,$user->id);              
                                }  
                            } 
                                                       
                            $blog->visibilities = \Helpers::getVisibilities($blog->id);
                            $blog->question = \Helpers::getQuestionsOptions($blog->id);
                            $blog->images = \Helpers::getBlogImages($blog->id,'768x428');
                            if($blog->background_image!=''){
                                $blog->background_image = url('uploads/blog/'.$blog->background_image);
                            }
                            if(count($blog->blog_sub_category)){
                                foreach($blog->blog_sub_category as $blog_sub_category){
                                    if($blog_sub_category->category!=''){
                                        if($blog_sub_category->category->image!=''){
                                            $blog_sub_category->category->image = url('uploads/category/'.$blog_sub_category->category->image);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return $this->sendResponse($blogs, __('lang.message_data_retrived_successfully'));
            }
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
}
