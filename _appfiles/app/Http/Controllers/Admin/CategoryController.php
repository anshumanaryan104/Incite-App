<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryTranslationRequest;
use GuzzleHttp\Client;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    **/
    public function index(Request $request)
    {
        try{
            $data['result'] = Category::getLists($request->all());
            $data['categories'] = Category::where('parent_id',0)->where('status',1)->get();
            return view('admin/category.index',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Store a newly created resource in category.
     *
     * @param  \App\Http\Requests\StoreCategoryRequest  $request
     * @return \Illuminate\Http\Response
    **/
    public function store(StoreCategoryRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = Category::addUpdate($request->all());
            if($added['status']==true){
                return redirect('admin/category')->with('success', $added['message']); 
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
     * Update the specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function update(UpdateCategoryRequest $request)
    {
        try{
            $validated = $request->validated();
            $updated = Category::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/category')->with('success', $updated['message']); 
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
    public function destroy($id)
    {
        try{
            $deleted = Category::deleteRecord($id);
            
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
    public function updateColumn($id,$name,$value)
    {
        try{
            $updated = Category::updateColumn($id,$name,$value);
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
     * Get translations of specified category from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function translation($id)
    {
        try{
            // if(setting('chat_gpt_api_key')==''){
            //     return redirect()->back()->with('error',__('lang.message_chat_gpt_key_not_found'));
            // }else{
            // }            
            $data['category'] = Category::getDetail($id);
            $data['languages'] = Category::getTranslation($id);
            return view('admin/category.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateCategoryTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = Category::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/category')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function translateByThirdParty(Request $request)
    {
        $post = $request->all();
        $data = \Helpers::translate($post['text'],$post['name']);
        // echo json_encode($data);exit;
        // $response = [
        //     'status' => true,
        //     'message' => __('lang.message_data_translated_successfully'),
        //     'data' => $data
        // ];
        return response($data);
    }

    /**
     * Update the translation of specified google in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function translateByGoogle(Request $request)
    {
        $post = $request->all();
        $data = \Helpers::googleTranslation($post['text'],$post['code']);
        return response($data);
    }
}
