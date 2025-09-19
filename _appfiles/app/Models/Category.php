<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use DB;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "categories";

    public function main_category(){
        return $this->hasOne('App\Models\Category',"id","parent_id");
    }

    public function sub_category(){
        return $this->hasMany('App\Models\Category',"parent_id","id");
    }
    
    /**
     * Fetch list of categories from here
    **/
    public static function getLists($search){
        try {
            $obj = new self;
            $categoryArr = array();
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['name']) && !empty($search['name'])){
                $obj = $obj->where('name', 'like', '%'.trim($search['name']).'%');
                $cat = Category::where('name', 'like', '%'.trim($search['name']).'%')->get();                
                if(count($cat)){
                    foreach($cat as $cat_data){
                        array_push($categoryArr,$cat_data->id);
                    }
                }
                
            }  
            if(isset($search['is_featured']) && $search['is_featured']!=''){
                $obj = $obj->where('is_featured',$search['is_featured']);
            }       
            if(isset($search['status']) && $search['status']!=''){
                $obj = $obj->where('status',$search['status']);
            }
            if(isset($categoryArr) && count($categoryArr)){
                $obj = $obj->orWhereIn('parent_id',$categoryArr);
            }
            // echo json_encode($obj);exit;
            $data = $obj->with('main_category')->paginate($pagination)->appends('perpage', $pagination);
            // latest('created_at')->
            if(count($data)){
                foreach($data as $row){
                    $row->blog_count = BlogCategory::where('category_id',$row->id)->count();
                }
            }
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Add or update category
    **/
    public static function addUpdate($data,$id=0) {
        try {
            $obj = new self;
            unset($data['_token']);
            if(isset($data['image']) && $data['image']!=''){
                $uploadImage = \Helpers::uploadFiles($data['image'],'category/');
                if($uploadImage['status']==true){
                    $data['image'] = $uploadImage['file_name'];
                }
            }
            if(!isset($data['parent_id'])){
                $data['parent_id'] = 0;
            }
            $slug = \Helpers::createSlug($data['name'],'category',$id,false);
            $data['slug'] = $slug;
            if($id==0){
                $data['created_at'] = date('Y-m-d H:i:s');
                $category_id = $obj->insertGetId($data);
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) {
                    $translation = array(
                        'category_id'=>$category_id,
                        'language_code'=>$language->code,
                        // 'name'=>\Helpers::translate($data['name'],$language->name),
                        'name'=>$data['name'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    $id_cat = CategoryTranslation::insertGetId($translation);
                }
                return ['status' => true, 'message' => 'Data added successfully.'];
            }
            else{
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $categoryTranslation = [
                    'name'=>$data['name'],
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = CategoryTranslation::where(['category_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $categoryTranslation['updated_at'] = date('Y-m-d H:i:s');
                    CategoryTranslation::where('id',$translationData->id)->update($categoryTranslation);
                }
                else
                {
                    $categoryTranslation['category_id'] = $id;
                    $categoryTranslation['language_code'] = $language_code;
                    $categoryTranslation['created_at'] = date('Y-m-d H:i:s');
                    CategoryTranslation::insert($categoryTranslation);
                }
                return ['status' => true, 'message' => "Data updated successfully."];
            }  
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Delete particular category
    **/
    public static function deleteRecord($id) {
        try {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            // CategoryTranslation::where('category_id',$id)->delete();
            return ['status' => true, 'message' => "Data deleted successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Update Columns 
    **/
    public static function updateColumn($id,$name,$value){
        try {
            $obj = new self;
            $data[$name] = $value;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $obj->where('id',$id)->update($data);
            return ['status' => true, 'message' => "Data changed successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Fetch particular category detail
    **/
    public static function getDetail($id){
        try {
            $obj = new self;
            $data = $obj->where('id',$id)->firstOrFail();
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Fetch languages data of particular category detail
    **/
    public static function getTranslation($id){
        try {
            $rowObj = new self;
            $rowObj = $rowObj->where('id',$id)->first();
            $data = Language::where('status',1)->get();
            if($rowObj){
                foreach ($data as $row) {
                    $row->details = CategoryTranslation::where('category_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'category_id' => $id,
                            'language_code' => $row->code,
                            'name' => $rowObj->name,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        CategoryTranslation::insert($postData);
                        $row->details = CategoryTranslation::where('category_id',$id)->where('language_code',$row->code)->first();
                    }
                }
            }
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Add or update category
    **/
    public static function updateTranslation($data,$id=0) {
        try {
            $obj = new self;
            for ($i=0; $i < count($data['language_code']); $i++) { 
                if($data['language_code'][$i] == 'en'){
                    $updateData = array(
                        'name' => $data['name'][$i]
                    );
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'name' => $data['name'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                CategoryTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Fetch list of most selected categories
    **/
    public static function getMostSelectedCategories(){
        try {
            $obj = new self;
            $categoryIdArr = array();

            $popularCategories = UserFeed::select('category_id', DB::raw('COUNT(*) as count'))->groupBy('category_id')->orderByDesc('count')->limit(5)->get();
            if(count($popularCategories)){
                foreach($popularCategories as $popularCategory){
                    if(!in_array($popularCategory->blog_id,$categoryIdArr)){
                        array_push($categoryIdArr,$popularCategory->category_id);
                    }
                }
            }
            $data = $obj->whereIn('id',$categoryIdArr)->with('main_category')->get();
            if(count($data)){
                foreach($data as $row){
                    $row->blog_count = BlogCategory::where('category_id',$row->id)->count();
                }
            }
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
