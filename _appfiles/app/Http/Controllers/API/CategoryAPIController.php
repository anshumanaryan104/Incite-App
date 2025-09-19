<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;
use Validator;

use App\Models\UserFeed;
use App\Models\User;

use Carbon\Carbon;


class CategoryAPIController extends Controller
{
    private $language;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        $this->language = $request->header('language-code') && $request->header('language-code') != '' ? $request->header('language-code') : 'en';
    }

    /**
     *  Add user feed
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doAddFeed(Request $request)
    {
        try{
            if($request->userAuthData){
                $validate = [
                    'category_id' => 'required'
                ];
                $validator = Validator::make($request->all(), $validate);
                if ($validator->fails()) {
                    return $this->sendError(__('lang.message_required_message'),$validator->errors());
                } 
                if(count($request->input('category_id'))){
                    UserFeed::whereNotIn('category_id',$request->input('category_id'))->where('user_id',$request->userAuthData->id)->delete();
                    foreach($request->input('category_id') as $category){
                        $checkFeed = UserFeed::where('category_id', $category)->where('user_id',$request->userAuthData->id)->first();
                        if (!$checkFeed) {
                            $postData = array(
                                'category_id'=>$category,
                                'created_at' => date("Y-m-d H:i:s")
                            );
                            if($request->header('api-token')!=''){
                                $user = User::where('api_token',$request->header('api-token'))->first();
                                if($user){
                                    $postData['user_id'] = $user->id;
                                }                
                            }  
                            UserFeed::insert($postData);
                        }                        
                    }
                    return $this->sendResponse([], __('lang.message_feeds_added_successfully'));
                }              
                return $this->sendError(__('lang.message_something_went_wrongs'));                
            }
            return $this->sendError(__('lang.message_user_not_found'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
}
