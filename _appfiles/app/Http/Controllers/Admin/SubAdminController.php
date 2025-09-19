<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Http\Requests\SubAdmin\StoreSubAdminRequest;
use App\Http\Requests\SubAdmin\UpdateSubAdminRequest;
use App\Models\Role;

use Illuminate\Http\Request;

class SubAdminController extends Controller
{
    /**
     * Display a listing of the data.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = User::getSubadminList($request->all());
            $data['roles'] = Role::where('name','!=','admin')->where('status',1)->get();
            return view('admin/subadmin.index',$data);
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created resource in data.
     *
     * @param  \App\Http\Requests\StoreSubAdminRequest  $request
     * @return \Illuminate\Http\Response
    **/
    public function store(StoreSubAdminRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = User::addUpdate($request->all());
            if($added['status']==true){
                return redirect('admin/sub-admin')->with('success', $added['message']); 
            }
            else{
                return redirect()->back()->with('error', $added['message']);
            }
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified data in storage.
     *
     * @param  \App\Http\Requests\UpdateSubAdminRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function update(UpdateSubAdminRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = User::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/sub-admin')->with('success', $updated['message']); 
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
     * Remove the specified resource from data.
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
}
