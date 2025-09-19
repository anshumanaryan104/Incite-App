<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Visibility;
use App\Http\Requests\Visibility\StoreVisibilityRequest;
use App\Http\Requests\Visibility\UpdateVisibilityRequest;
use App\Http\Requests\Visibility\UpdateVisibilityTranslationRequest;

class VisibilityController extends Controller
{
    /**
     * Display a listing of the entry.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = Visibility::getLists($request->all());
            return view('admin/visibility.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created entry in storage.
     *
     * @param  \App\Http\Requests\StoreVisibilityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreVisibilityRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = Visibility::addUpdate($request->all());
            if($added['status']){
                return redirect('admin/visibility')->with('success', $added['message']); 
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
     * Update the specified entry in storage.
     *
     * @param  \App\Http\Requests\UpdateVisibilityRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateVisibilityRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = Visibility::addUpdate($request->all(),$request->input('id'));
            if($updated['status']){
                return redirect('admin/visibility')->with('success', $updated['message']); 
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
     * Remove the specified entry from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = Visibility::deleteRecord($id);
            if($deleted['status']){
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
     * change status of the specified entry from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function changeStatus($id,$status)
    {
        try{
            $updated = Visibility::changeStatus($status,$id);
            if($updated['status']){
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
     * Get translations of specified entry from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function translation($id)
    {
        try{
            $data['detail'] = Visibility::getDetail($id);
            $data['languages'] = Visibility::getTranslation($id);
            return view('admin/visibility.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified entry in storage.
     *
     * @param  \App\Http\Requests\UpdateVisibilityTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateVisibilityTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = Visibility::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/visibility')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
}
