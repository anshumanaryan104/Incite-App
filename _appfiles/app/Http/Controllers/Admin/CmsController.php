<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CmsContent;
use App\Http\Requests\Cms\StoreCmsRequest;
use App\Http\Requests\Cms\UpdateCmsRequest;
use App\Http\Requests\Cms\UpdateCmsTranslationRequest;

class CmsController extends Controller
{
    /**
     * Display a listing of the cms.
     * @return \Illuminate\Http\Response
    **/
    public function index(Request $request)
    {
        try {
            $data['result'] = CmsContent::getLists($request->all());
            return view('admin/cms.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Show the form for creating a new cms.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try{
            return view('admin/cms.create');
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Store a newly created resource in cms.
     *
     * @param  \App\Http\Requests\StoreCmsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCmsRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = CmsContent::addUpdate($request->all());
            if($added['status']==true){
                return redirect('admin/cms')->with('success', $added['message']); 
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
     * Show the form for editing the specified cms.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try{
            $data['row'] = CmsContent::getDetail($id);
            return view('admin/cms.edit',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCmsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCmsRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = CmsContent::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/cms')->with('success', $updated['message']); 
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
     * Remove the specified category from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function changeStatus($id,$status)
    {
        try{
            $updated = CmsContent::changeStatus($status,$id);
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
     * Remove the specified resource from cms.
     *
     * @param  \App\Models\Cms  $cms
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = CmsContent::deleteRecord($id);
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
     * Get translations of specified category from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function translation($id)
    {
        try{
            $data['detail'] = CmsContent::getDetail($id);
            $data['languages'] = CmsContent::getTranslation($id);
            return view('admin/cms.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateCmsTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateCmsTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = CmsContent::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/cms')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
}
