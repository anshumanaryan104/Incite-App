<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Auth;
use Validator;

use App\Models\User;
use App\Models\DeviceToken;
use App\Models\UserLogin;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UserAPIController extends Controller
{
    private $language;
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->request = $request;
        $this->language = $request->header('language-code') && $request->header('language-code') != '' ? $request->header('language-code') : 'en';
    }

    function doLogin(Request $request)
    {
        $validate = [
            'email' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return $this->sendError(__('lang.message_required_message'),$validator->errors());
        }
        $user = User::where('email', $request->input('email'))->where('type','user')->first();
        if($user){
            if($user->status==1){
                if(auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])){
                    $user = auth()->user();
                    $user->api_token = \Helpers::generateApiToken();
                    User::where('id',$user->id)->update(['api_token'=>$user->api_token]);
                    $loginUser = array(
                        'user_id'=>$user->id,
                        'login_date_time'=>date('Y-m-d h:i:s')
                    );
                    if($user->photo!=''){
                        $user->photo = url('uploads/user/'.$user->photo);
                    }
                    UserLogin::insert($loginUser);
                    $checkToken = DeviceToken::where('player_id',$request->input('player_id'))->first();
                    if ($checkToken) {
                        $postData = array(
                            'user_id' => $user->id,
                            'updated_at' => date("Y-m-d H:i:s")
                        );
                        if($checkToken->user_id==0){
                            $postData['is_notification_enabled'] = $checkToken->is_notification_enabled;
                        }else{
                            $postData['is_notification_enabled'] = 1;
                        }
                        DeviceToken::where('id', $checkToken->id)->update($postData);
                    }else{
                        $postData = array(
                            'user_id' => $user->id,
                            'player_id' => $request->input('player_id'),
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        DeviceToken::insertGetId($postData);
                    }
                    return $this->sendResponse($user,__('lang.message_login_success'));
                }else{
                    return $this->sendError(__('lang.message_wrong_email_password'));
                }
            }else{
                return $this->sendError(__('lang.message_user_inactive'));
            }
        }
        return $this->sendError(__('lang.message_user_not_found'));
    }

    function doSignup(Request $request)
    {
        $validate = [
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ];
        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return $this->sendError(__('lang.message_required_message'),$validator->errors());
        }
        $user = User::where('email', $request->input('email'))->first();
        if(!$user){
            $postdata = array(
            	'type'=>'user',
                'name' => $request->name,
                'email' => $request->email,
                'login_from'=>'email',
                'api_token'=>\Helpers::generateApiToken(),
                'status' => 1,
                'password' => Hash::make($request->password),
                'created_at'=>date("Y-m-d H:i:s")
            );
            $user_id = User::insertGetId($postdata);  
            if($user_id){
                $userData =  User::where('id', $user_id)->first();
                if($userData){
                    if($userData->photo!=''){
                        $userData->photo = url('uploads/user/'.$userData->photo);
                    }
                    $checkToken = DeviceToken::where('player_id',$request->player_id)->first();
                    if ($checkToken) {
                        $postData = array(
                            'user_id'=>$user_id,
                            'updated_at' => date("Y-m-d H:i:s")
                        );
                        if($checkToken->user_id==0){
                            $postData['is_notification_enabled'] = $checkToken->is_notification_enabled;
                        }else{
                            $postData['is_notification_enabled'] = 1;
                        }
                        DeviceToken::where('id', $checkToken->id)->update($postData);
                    }else{
                        $postData = array(
                            'user_id' => $user_id,
                            'player_id' => $request->player_id,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        DeviceToken::insertGetId($postData);
                    }
                }
            }
            return $this->sendResponse($userData,__('lang.message_signup_success'));
        }
        return $this->sendError(__('lang.message_user_already_exist'));
    }

    function doForgetPassword(Request $request)
    {
        $validate = [
            'email' => 'required'
        ];
        $validator = Validator::make($request->all(), $validate);
        if ($validator->fails()) {
            return $this->sendError(__('lang.message_required_message'),$validator->errors());
        }
        $user = User::where('email', $request->input('email'))->where('type','user')->first();
        if($user){
            $otp = rand(1000,9999);             
            $data = array();
            $data['otp'] = $otp;    
            $data['name'] = $user->name;
            $data['text'] = "Here is a one-time verification code for your use: ".$otp;
            //Todo Email
            User::where('id',$user->id)->update(['otp'=>$otp]);
            return $this->sendResponse($data,__('lang.message_otp_sent_success'));
        }
        return $this->sendError(__('lang.message_user_not_found'));
    }

    function doResetPassword(Request $request)
    {
        $validate = [
            'otp' => 'required',
            'password' => 'required',
            'cpassword' => 'required'
        ];
        $validator = Validator::make($request->all(), $validate);
        if ($validator->fails()) {
            return $this->sendError(__('lang.message_required_message'),$validator->errors());
        }
        $user = User::where('otp', $request->input('otp'))->where('type','user')->first();
        if($user){
            $inject = array();
            if($request->input('password') && $request->input('password') != ''){
                $inject['password'] = bcrypt($request->input('password'));
                $inject['otp'] = 0;
            }
            User::where('id',$user->id)->update($inject);
            return $this->sendResponse($user,__('lang.message_password_reset_success'));
        }
        return $this->sendError(__('lang.message_user_not_found'));
    }

    function getProfile(Request $request){
        try{
            if($request->userAuthData){
                $user = User::where('id',$request->userAuthData->id)->where('status',1)->first();
                if($user){
                    if($user->photo!=''){
                        $user->photo = url('uploads/user/'.$user->photo);
                    }
                }
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
                $response = $this->sendResponse($user, __('lang.message_data_retrived_successfully'));            
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
            }
            return $this->sendError(__('lang.message_user_not_found'));
        }catch(RepositoryException $e){
            return $this->sendError($e->getMessage());
        }
    }

    /**
     *  update profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doUpdateProfile(Request $request)
    {
        try{
            if($request->userAuthData){
                $post = $request->all();
                $user = $request->userAuthData;
                $post['photo'] = $request->file('photo');
                $checkuser = User::where('email', $request->input('email'))->where('id','!=',$user->id)->first();
                if ($checkuser) {
                    return $this->sendError(__('lang.message_email_already_exist'));
                }
                $postData = array(
                    'updated_at' => date("Y-m-d H:i:s")
                );
                if(isset($post['name'])){
                    $postData['name'] = $post['name'];
                }
                if(isset($post['phone'])){
                    $postData['phone'] = $post['phone'];
                }
                if(isset($post['email'])){
                    $postData['email'] = $post['email'];
                }
                if(isset($post['photo'])){
                    $uploadImage = \Helpers::uploadFiles($request->file('photo'),'user/');
                    if($uploadImage['status']==true)
                    {
                        $postData['photo'] = $uploadImage['file_name'];
                    }
                }
                
                User::where('id',$user->id)->update($postData);
                $userProfile = User::where('id',$user->id)->first();
                if($userProfile){
                    if($userProfile->photo!=''){
                        $userProfile->photo = url('uploads/user/'.$userProfile->photo);
                    }
                }
                return $this->sendResponse($userProfile, __('lang.message_profile_updated_successfully'));
            }
            return $this->sendError(__('lang.message_user_not_found'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     *  Change password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doChangePassword(Request $request)
    {
        try{
            if($request->userAuthData){
                $post = $request->all();
                $user = $request->userAuthData;
                $checkuser = User::where('id',$user->id)->first();
                if (!$checkuser) {
                    return $this->sendError(__('lang.message_email_already_exist'));
                }
                if(isset($post['old_password']) && $post['old_password']!=''){
                    if(!Hash::check($post['old_password'],$checkuser->password)){
                        return $this->sendError(__('lang.message_old_password_is_wrong'));
                    }
                }
                $postData = array(
                    'updated_at' => date("Y-m-d H:i:s")
                );
                if(isset($post['password'])){
                    $postData['password'] = Hash::make($post['password']);
                }                
                User::where('id',$user->id)->update($postData);
                $userProfile = User::where('id',$user->id)->first();
                if($userProfile){
                    if($userProfile->photo!=''){
                        $userProfile->photo = url('uploads/user/'.$userProfile->photo);
                    }
                }
                return $this->sendResponse($userProfile, __('lang.message_password_changed_successfully'));
            }
            return $this->sendError(__('lang.message_user_not_found'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     *  update profile
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doUpdateToken(Request $request)
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
            $checkToken = DeviceToken::where('player_id',$post['player_id'])->first();
            // where('user_id',$user_id)->
            if ($checkToken) {
                $postData = array(
                    'updated_at' => date("Y-m-d H:i:s")
                );
                if($checkToken->user_id==0){
                    $postData['user_id'] = $user_id;
                }else if($checkToken->user_id!=$user_id){
                    $postData['user_id'] = $user_id;
                }
                if(isset($post['is_notification_enabled'])){
                    $postData['is_notification_enabled'] = $post['is_notification_enabled'];
                }
                DeviceToken::where('id', $checkToken->id)->update($postData);
            }else{
                if($post['player_id']!='' && $post['player_id']!='null'){
                    $postData = array(
                        'user_id' => $user_id,
                        'player_id' => $post['player_id'],
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    if(isset($post['is_notification_enabled'])){
                        $postData['is_notification_enabled'] = $post['is_notification_enabled'];
                    }
                    DeviceToken::insertGetId($postData);
                }                
            }
            return $this->sendResponse([], __('lang.message_data_updated_successfully'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    function doDeleteAccount(Request $request){
        try{
            if($request->userAuthData){
                $user = $request->userAuthData;
                User::where('id',$user->id)->delete();
                return $this->sendResponse([], __('lang.message_user_account_deleted_successfully'));
            }
            return $this->sendError(__('lang.message_user_not_found'));
        }catch(RepositoryException $e){
            return $this->sendError($e->getMessage());
        }
    }

     /**
     *  Get notification detail
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doGetNotificationDetail(Request $request)
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
            $checkToken = DeviceToken::where('user_id',$user_id)->where('player_id',$post['player_id'])->first();
            return $this->sendResponse($checkToken, __('lang.message_data_fetched_successfully'));
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    function doSocialMediaSignup(Request $request)
    {
        if($request->input('login_from')!='apple'){
            $validate = [
                'name' => 'required',
                'email' => 'required',
            ];
        }else{
            $validate = [
                'apple_token' => 'required',
            ];
        }
        $validator = Validator::make($request->all(), $validate);

        if ($validator->fails()) {
            return $this->sendError(__('lang.message_required_message'),$validator->errors());
        }
        if($request->input('login_from')!='apple'){
            $userexist = User::where('email',$request->input('email'))->first();
        }else{
            $userexist = User::where('apple_token',$request->input('apple_token'))->first();
        }
        if(!$userexist){
            $userData = array(
                'type'=>'user',
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'login_from'=>$request->input('login_from'),
                'api_token'=>\Helpers::generateApiToken(),
                'status' => 1,
                'created_at'=>date("Y-m-d H:i:s")
            );
            if($request->input('phone') !==''){
                $userData['phone'] = $request->input('phone');
            }

            if($request->input('fb_token')!=''){
                $userData['fb_token'] = $request->input('fb_token');
            }

            if($request->input('google_token')!=''){
                $userData['google_token'] = $request->input('google_token');
            }

            if($request->input('apple_token')!=''){
                $userData['apple_token'] = $request->input('apple_token');
            }

            if($request->input('device_token')!=''){
                $userData['device_token'] = $request->input('device_token');
            }
            $image = '';
            if($request->input('login_from')=='facebook' && $request->input('fb_id')!=''){
                $image = "https://graph.facebook.com/v2.9/".$request->input('fb_id')."/picture?width=360&height=360";
            }else{
                $image = $request->input('image');
            }
            if($image!=''){
                $photo = \Helpers::uploadFilesThroughUrl($image,'user/');
                $userData['photo'] = "";
                if($photo['status']){
                    $userData['photo'] = $photo['file_name'];
                }
            }
            $user_id = User::insertGetId($userData);            
            $registered_user =  User::where('id', $user_id)->first();
            if($registered_user){
                $registered_user->is_new_user = true;
                if($registered_user->photo!=''){
                    $registered_user->photo = url('uploads/user/'.$registered_user->photo);
                }
                $checkToken = DeviceToken::where('player_id',$request->player_id)->first();
                if ($checkToken) {
                    $postData = array(
                        'user_id'=>$user_id,
                        'updated_at' => date("Y-m-d H:i:s")
                    );
                    if($checkToken->user_id==0){
                        $postData['is_notification_enabled'] = $checkToken->is_notification_enabled;
                    }else{
                        $postData['is_notification_enabled'] = 1;
                    }
                    DeviceToken::where('id', $checkToken->id)->update($postData);
                }else{
                    $postData = array(
                        'user_id' => $user_id,
                        'player_id' => $request->player_id,
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    DeviceToken::insertGetId($postData);
                }
            }
            return $this->sendResponse($registered_user,__('lang.message_signup_success'));
        }else{
            $registered_user = $userexist;
            if($registered_user){
                $registered_user->is_new_user = false;
                if($registered_user->photo!=''){
                    $registered_user->photo = url('uploads/user/'.$registered_user->photo);
                }
                $checkToken = DeviceToken::where('player_id',$request->player_id)->first();
                if ($checkToken) {
                    $postData = array(
                        'user_id' => $registered_user->id,
                        'updated_at' => date("Y-m-d H:i:s")
                    );
                    if($checkToken->user_id==0){
                        $postData['is_notification_enabled'] = $checkToken->is_notification_enabled;
                    }else{
                        $postData['is_notification_enabled'] = 1;
                    }
                    DeviceToken::where('id', $checkToken->id)->update($postData);
                }else{
                    $postData = array(
                        'user_id' => $registered_user->id,
                        'player_id' => $request->player_id,
                        'created_at' => date("Y-m-d H:i:s")
                    );
                    DeviceToken::insertGetId($postData);
                }
            }
            return $this->sendResponse($registered_user,__('lang.message_signup_success'));
        }
        return $this->sendError(__('lang.message_something_went_wrong'));
    }
}
