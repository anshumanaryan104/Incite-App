<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Http\Requests\Quote\StoreQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteRequest;
use App\Http\Requests\Quote\UpdateQuoteTranslationRequest;

class QuoteController extends Controller
{
    /**
     * Display a listing of the live news.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        try {
            $data['result'] = Quote::getLists($request->all());
            return view('admin/quote.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created live news in storage.
     *
     * @param  \App\Http\Requests\StoreQuoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreQuoteRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = Quote::addUpdate($request->all());
            if($added['status']){
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
     * Update the specified live news in storage.
     *
     * @param  \App\Http\Requests\UpdateLiveNewsRequest  $request
     * @param  \App\Models\LiveNews  $liveNews
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateQuoteRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = Quote::addUpdate($request->all(),$request->input('id'));
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
     * Remove the specified live news from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = Quote::deleteRecord($id);
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
            $updated = Quote::changeStatus($status,$id);
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
            $data['detail'] = Quote::getDetail($id);
            $data['languages'] = Quote::getTranslation($id);
            return view('admin/quote.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateQuoteTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateQuoteTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = Quote::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect()->back()->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function translateByThirdParty(Request $request)
    {
        $post = $request->all();
        $data = \Helpers::translate($post['text'],$post['name']);
        $response = [
            'status' => true,
            'message' => "Data translated successfully.",
            'data' => $data
        ];
        return response($response);
    }
}
