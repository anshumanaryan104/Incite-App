<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveNews;
use App\Http\Requests\LiveNews\StoreLiveNewsRequest;
use App\Http\Requests\LiveNews\UpdateLiveNewsRequest;
use App\Http\Requests\LiveNews\UpdateLiveNewsTranslationRequest;

class LiveNewsController extends Controller
{
    /**
     * Display a listing of the live news.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = LiveNews::getLists($request->all());
            return view('admin/live-news.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created live news in storage.
     *
     * @param  \App\Http\Requests\StoreLiveNewsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreLiveNewsRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = LiveNews::addUpdate($request->all());
            if($added['status']){
                return redirect('admin/live-news')->with('success', $added['message']); 
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
     * Display the specified live news.
     *
     * @param  \App\Models\LiveNews  $liveNews
     * @return \Illuminate\Http\Response
     */
    public function show(LiveNews $liveNews)
    {
        try{
            $data['row'] = LiveNews::getDetail($liveNews);
            return view('admin/live-news.detail',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Show the form for editing the specified live news.
     *
     * @param  \App\Models\LiveNews  $liveNews
     * @return \Illuminate\Http\Response
     */
    public function edit(LiveNews $liveNews)
    {
        try{
            $data['row'] = LiveNews::getDetail($liveNews);
            return view('admin/live-news.edit',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified live news in storage.
     *
     * @param  \App\Http\Requests\UpdateLiveNewsRequest  $request
     * @param  \App\Models\LiveNews  $liveNews
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateLiveNewsRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = LiveNews::addUpdate($request->all(),$request->input('id'));
            if($updated['status']){
                return redirect('admin/live-news')->with('success', $updated['message']); 
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
     * Remove the specified live news from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = LiveNews::deleteRecord($id);
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
     * Remove the specified category from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function changeStatus($id,$status)
    {
        try{
            $updated = LiveNews::changeStatus($status,$id);
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
            $data['detail'] = LiveNews::getDetail($id);
            $data['languages'] = LiveNews::getTranslation($id);
            return view('admin/live-news.translation',$data);
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
    public function updateTranslation(UpdateLiveNewsTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = LiveNews::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/live-news')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
}
