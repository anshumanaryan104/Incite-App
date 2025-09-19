<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function assignRole($role)
    {
        $this->roles()->sync($role, false);
    }

    /**
    * Fetch list of user from here
    **/
    public static function getLists($search){
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['search']) && !empty($search['search']))
            {
                // $obj = $obj->whereLike('name',trim($search['name']));
                $keyword = $_GET['search'];
                $obj = $obj->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('phone'),'like','%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'),'like','%'.strtolower($keyword). '%');
                });
            }         
            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }

            $data = $obj->where('type','user')->latest('created_at')->paginate($pagination)->appends('perpage', $pagination);
            return $data;
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
    * Fetch list of subadmin from here
    **/
    public static function getSubadminList($search){
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['search']) && !empty($search['search']))
            {
                $keyword = $_GET['search'];
                $obj = $obj->where(function($q) use ($keyword){
                    $q->where(DB::raw('LOWER(name)'), 'like', '%'.strtolower($keyword). '%')
                    ->orWhere(DB::raw('email'),'like','%'.strtolower($keyword). '%');
                });
            }         
            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }

            $data = $obj->where('type','subadmin')->latest('created_at')->paginate($pagination)->appends('perpage', $pagination);
            return $data;
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Add or update category
    **/
    public static function addUpdate($data,$id=0) {
        $obj = new self;
            unset($data['_token']);
            if(isset($data['photo']) && $data['photo']!=''){
                $uploadImage = \Helpers::uploadFiles($data['photo'],'user/');
                if($uploadImage['status']==true){
                    $data['photo'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['password']) && $data['password']!=''){
                $data['password'] = Hash::make($data['password']);
            }
            if($id==0){
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['type'] = "subadmin";
                $entry_id = $obj->insertGetId($data);
                $user= User::find($entry_id);
                $role = Role::where('id',$data['role_id'])->first();
                $permissions = DB::table('role_has_permissions')->where('role_id',$data['role_id'])->pluck('permission_id')->all();
                $role->syncPermissions($permissions);
                $user->assignRole([$data['role_id']]);
                return ['status' => true, 'message' => 'Data added successfully.'];
            }
            else{
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                return ['status' => true, 'message' => "Data updated successfully."];
            }  
        // try {
        //     $obj = new self;
        //     unset($data['_token']);
        //     if(isset($data['photo']) && $data['photo']!=''){
        //         $uploadImage = \Helpers::uploadFiles($data['photo'],'category/');
        //         if($uploadImage['status']==true){
        //             $data['photo'] = $uploadImage['file_name'];
        //         }
        //     }
        //     if($id==0){
        //         $data['created_at'] = date('Y-m-d H:i:s');
        //         $entry_id = $obj->insertGetId($data);
        //         return ['status' => true, 'message' => 'Data added successfully.'];
        //     }
        //     else{
        //         $data['updated_at'] = date('Y-m-d H:i:s');
        //         $obj->where('id',$id)->update($data);
        //         return ['status' => true, 'message' => "Data updated successfully."];
        //     }  
        // }
        // catch (\Exception $e) {
        //     return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        // }
    }

    /**
     * Delete particular data
    **/

    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            return ['status' => true, 'message' => "Data deleted successfully."];
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Update Columns 
    **/
    public static function updateColumn($id,$value){
        try {
            $obj = new self;
            $data['status'] = $value;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $obj->where('id',$id)->update($data);
            return ['status' => true, 'message' => "Data changed successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * admin Login
    **/
    public static function adminLogin($data){
        try {
            $obj = new Self;
            $credentials = [
                'email' => $data['email'],
                'password' => $data['password'],
            ];
            $user = $obj->where('email',$data['email'])->where('type','!=','user')->first();
            if ($user) {
                $credentials['type'] = $user->type;
                if($user->status != 1){
                    return ['status' => false, 'message' => config('constant.common.messages.your_account_has_been_suspended')];
                }
                if (Auth::attempt($credentials)) {
                    return ['status' => true, 'message' => 'You have successfully logged in. Now you can start to explore! 👋 Welcome Admin!'];
                }else{
                    if ($obj->where('email', $data['email'])->exists()) {
                        return ['status' => false, 'message' => "The password is incorrect."];
                    }
                    return ['status' => false, 'message' => "Invalid email and password."];
                }
            }else{
                return ['status' => false, 'message' => "Invalid email and password."];
            }
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Admin Forget Password
    **/
    public static function adminForgetPassword($data){
        try {
            $obj = new Self;
           
            $credentials = [
                'email' => $data['email'],
            ];
            $user = $obj->where('email',$data['email'])->first();
            // where('type','!=','user')->
            if ($user) {
                if($user){
                    $otp = rand(1000,9999);             
                    $data = array();
                    $data['otp'] = $otp;    
                    $data['name'] = $user->name;
                    $data['text'] = "Here is a one-time verification code for your use: ".$otp;
                    //Todo Email
                    $c = \Helpers::sendEmail('emails.forgot-password',$data,$user->email,$user->name, setting('site_name'). ' forgot password '.$data['otp'], setting('site_name'),'8210jp@gmail.com','');
                    User::where('id',$user->id)->update(['otp'=>$otp]);
                    return ['status' => true, 'data' => $data, 'message' => __('lang.message_otp_sent_success')];
                }
            }else{
                return ['status' => false, 'message' => __('lang.message_user_not_found')];
            }
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Admin Reset Password
    **/
    public static function adminResetPassword($data){
        $obj = new Self;
            $credentials = [
                'otp' => $data['otp'],
                'password' => $data['password'],
                'cpassword' => $data['cpassword'],
            ];
            $user = $obj->where('otp',$data['otp'])->where('type','!=','user')->first();
            if ($user) {
                if($user){
                    $inject = array();
                    if($data['password'] && $data['password'] != ''){
                        $inject['password'] = bcrypt($data['password']);
                        $inject['otp'] = 0;
                    }
                    User::where('id',$user->id)->update($inject);
                    return ['status' => true, 'data' => $user, 'message' => __('lang.message_password_reset_success')];
                }
            }else{
                return ['status' => false, 'message' => __('lang.message_user_not_found')];
            }
        // try {
        //     $obj = new Self;
        //     $credentials = [
        //         'otp' => $data['otp'],
        //         'password' => $data['password'],
        //         'cpassword' => $data['cpassword'],
        //     ];
        //     $user = $obj->where('otp',$data['otp'])->where('type','!=','user')->first();
        //     if ($user) {
        //         if($user){
        //             $inject = array();
        //             if($data['password'] && $data['password'] != ''){
        //                 $inject['password'] = bcrypt($data['password']);
        //                 $inject['otp'] = 0;
        //             }
        //             User::where('id',$user->id)->update($inject);
        //             return ['status' => true, 'data' => $user, 'message' => __('lang.message_password_reset_success')];
        //         }
        //     }else{
        //         return ['status' => false, 'message' => __('lang.message_user_not_found')];
        //     }
        // }
        // catch (\Exception $e) {
        //     return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        // }
    }
    
    /**
     * Check if user has role Admin and Subadmin
     * @param $userId
     * @return bool
     */
    public static function isAdminSubadmin($userId,$role_id) 
    {
        $roles = Role::where('id',$role_id)->first();
        if($roles)
        {
            $roleName = $roles->name;
        }
        return self::checkUserRole($userId, $roleName);
    }

    /**
     * Fetch particular admin profile detail
    **/
    public static function getProfile()
    {
        try 
        {
            $obj = new self;
            $id = Auth::user()->id;
            $data = $obj->where('id',$id)->firstOrFail();
            return $data;
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Update admin profile
    **/
    public static function updateProfile($data,$id) 
    {
        try 
        {
            $obj = new self;
            unset($data['_token']);
            if(isset($data['photo']) && $data['photo']!='')
            {
                $uploadImage = \Helpers::uploadFiles($data['photo'],'user/');
                if($uploadImage['status']==true)
                {
                    $data['photo'] = $uploadImage['file_name'];
                }
            }
            if (empty($data['password'])) 
            {
                unset($data['password']);
            } 
            else 
            {
                $data['password'] = Hash::make($data['password']);
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            $obj->where('id',$id)->update($data);
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }

    }
}
