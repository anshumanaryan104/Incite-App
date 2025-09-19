<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Models\SocialMediaLink;
use App\Http\Requests\SocialMediaLink\StoreSocialMediaLinkRequest;
use App\Http\Requests\SocialMediaLink\UpdateSocialMediaLinkRequest;
use Illuminate\Http\Request;

class SocialMediaLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['result'] = SocialMediaLink::getLists($request->all());
        return view('admin/setting.social.index',$data);
        // try{
        //     $data['result'] = SocialMediaLink::getLists($request->all());
        //     return view('admin/setting.social.index',$data);
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSocialMediaLinkRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSocialMediaLinkRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = SocialMediaLink::addUpdate($request->all());
            if($added['status']==true){
                return redirect()->back()->with('success', $added['message']); 
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
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSocialMediaLinkRequest  $request
     * @param  \App\Models\SocialMediaLink  $SocialMediaLink
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSocialMediaLinkRequest $request, SocialMediaLink $SocialMediaLink)
    {
        try{
            $validated = $request->validated();
            $updated = SocialMediaLink::addUpdate($request->all(),$request->input('id'));
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
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = SocialMediaLink::deleteRecord($id);
            if($deleted['status']==true){
                return redirect()->back()->with('success', $deleted['message']); 
            }
            else{
                return redirect()->back()->with('error', $deleted['message']);
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
            $updated = SocialMediaLink::updateColumn($id,$value);
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
