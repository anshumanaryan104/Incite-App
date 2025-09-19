<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\UserFeed;

use App\Http\Requests\User\UpdateProfileRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the user.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = User::getLists($request->all());
            return view('admin/user.index',$data);
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Display the specified user personalization detail.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
    */
    public function show(User $user)
    {
        try{
            $data['personalization_detail'] = User::getPersonalizationDetail($user);
            return view('admin/user.detail',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Remove the specified resource from user.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        try{
            $userDeleted = User::deleteRecord($id);
            if($userDeleted['status']==true){
                return redirect()->back()->with('success', $userDeleted['message']); 
            }
            else{
                return redirect()->back()->with('error', $userDeleted['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * update the specified column from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateColumn($id,$value)
    {
        try{
            $updated = User::updateColumn($id,$value);
            if($updated['status']==true){
                return redirect()->back()->with('success', $updated['message']); 
            }
            else{
                return redirect()->back()->with('error', $updated['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    
    /**
     * Show the form for editing the specified user profile.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
    */
    public function profile()
    {
        try {
            $data['row'] = User::getProfile();
            return view('admin/profile.index',$data);
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    
    /**
     * Update the specified resource in user.
     *
     * @param  \App\Http\Requests\User\UpdateProfileRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function updateProfile(UpdateProfileRequest $request)
    {
        try{
            $validated = $request->validated();
            $profileUpdated = User::updateProfile($request->all(),$request->input('id'));
            if($profileUpdated['status']==true){
                return redirect()->back()->with('success', $profileUpdated['message']); 
            }
            else{
                return redirect()->back()->with('error', $profileUpdated['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Display a listing of the user.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function personalization(Request $request,$id)
    {
        $data['result'] = UserFeed::where('user_id',$id)->with('category')->paginate(config('constant.pagination'))->appends('perpage', config('constant.pagination'));
        // echo json_encode($data);exit;
        return view('admin/user.personalization',$data);
        // try {
        //     $data['result'] = User::getLists($request->all());
        //     return view('admin/personalization.index',$data);
        // } catch (\Exception $ex) {
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        // }
    }
}
