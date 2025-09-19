<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EPaper;
use App\Http\Requests\Epaper\StoreEpaperRequest;
use App\Http\Requests\Epaper\UpdateEpaperTranslationRequest;
use App\Http\Requests\Epaper\UpdateEpaperRequest;

class EpaperController extends Controller
{
    /**
     * Display a listing of the epaper.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = EPaper::getLists($request->all());
            return view('admin/e-paper.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Show the form for creating a new epaper.
     * @return \Illuminate\Http\Response
    */
    public function create()
    {
        try {
            return view('admin/e-paper.create');
        } catch (\Exception $ex) {
            echo json_encode($ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());exit;
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created resource in epaper.
     * @param  \App\Http\Requests\StoreEpaperRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function store(StoreEpaperRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = EPaper::addUpdate($request->all());
            if($added['status']==true){
                return redirect('admin/e-papers')->with('success', $added['message']); 
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
     * Display the specified epaper.
     * @param  \App\Models\Request $request
     * @return \Illuminate\Http\Response
    */
    public function show(Request $request)
    {
        try{
            $data['detail'] = EPaper::getDetail($epaper);
            return view('admin/e-paper.detail',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Show the form for editing the specified epaper.
     * @param  \App\Models\EPaper  $epaper
     * @return \Illuminate\Http\Response
    */
    public function edit(Request $request)
    {
        try{
            $data['detail'] = EPaper::getDetail($request);
            return view('admin/e-paper.edit',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified resource in epaper.
     * @param  \App\Http\Requests\UpdateEpaperRequest  $request
     * @return \Illuminate\Http\Response
    */
    public function update(UpdateEpaperRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = EPaper::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/e-papers')->with('success', $updated['message']); 
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
     * Remove the specified resource from epaper.
     * @param  Request $request
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        try{
            $deleted = EPaper::deleteRecord($id);
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
     * Remove the specified category from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function changeStatus($id,$status)
    {
        try{
            $updated = EPaper::changeStatus($status,$id);
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
     * Get translations of specified category from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function translation($id)
    {
        try{
            $data['detail'] = EPaper::getDetail($id);
            $data['languages'] = EPaper::getTranslation($id);
            return view('admin/e-paper.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateLiveNewsTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateEpaperTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = EPaper::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/e-papers')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
}
