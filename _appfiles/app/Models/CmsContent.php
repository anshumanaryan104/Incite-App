<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CmsContent extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "cms_contents";

    /**
     * Fetch list of live news from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['title']) && !empty($search['title']))
            {
                $obj = $obj->where('title', 'like', '%'.trim($search['title']).'%');
            }   
            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }
            $data = $obj->latest('created_at')->paginate($pagination)->appends('perpage', $pagination);
            return $data;
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Fetch particular epaper detail
    **/
    public static function getDetail($id)
    {
        try 
        {
            $obj = new self;
            $data = $obj->where('id',$id)->firstOrFail();
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Add or update epaper
    **/
    public static function addUpdate($data, $id=0)
    {
        try 
        {
    		$obj = new self;
            unset($data['_token']);
            if(isset($data['image']) && $data['image']!=''){
                $uploadImage = \Helpers::uploadFiles($data['image'],'cms/');
                if($uploadImage['status']==true){
                    $data['image'] = $uploadImage['file_name'];
                }
            }
            $slug = \Helpers::createSlug($data['title'],'cms',$id,false);
            $data['page_title'] = $slug;
            if($id==0){
                $data['created_at'] = date('Y-m-d H:i:s');
                $entry_id = $obj->insertGetId($data);
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'cms_id'=>$entry_id,
                        'language_code'=>$language->code,
                        'title'=>$data['title'],
                        'description'=>$data['description'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    CmsContentTranslation::insert($translation);
                }
                return ['status' => true, 'message' => "Data added successfully."];
            }
            else
            {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $translation = [
                    'title'=>$data['title'],
                    'description'=>$data['description'],
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = CmsContentTranslation::where(['cms_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $translation['updated_at'] = date('Y-m-d H:i:s');
                    CmsContentTranslation::where('id',$translationData->id)->update($translation);
                }
                else
                {
                    $translation['cms_id'] = $id;
                    $translation['language_code'] = $language_code;
                    $translation['created_at'] = date('Y-m-d H:i:s');
                    CmsContentTranslation::insert($translation);
                }
                return ['status' => true, 'message' => "Data updated successfully."];
            }
        } 
        catch (\Exception $e) 
        {
    		return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
    	}
    }

    /**
     * Delete particular epaper
    **/
    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            CmsContentTranslation::where('live_news_id',$id)->delete();
            return ['status' => true, 'message' => config('constant.common.messages.success_delete')];
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    
    /**
     * Update Columns 
    **/
    public static function changeStatus($value, $id)
    {
        try {
            $obj = new self;
            $data['status'] = $value;
            $data['updated_at'] = date('Y-m-d H:i:s');
            $obj->where('id',$id)->update($data);
            return ['status' => true, 'message' => "Data changed successfully."];
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
                    $row->details = CmsContentTranslation::where('cms_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'cms_id' => $id,
                            'language_code' => $row->code,
                            'title' => $rowObj->title,
                            'description' => $rowObj->description,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        CmsContentTranslation::insert($postData);
                        $row->details = CmsContentTranslation::where('cms_id',$id)->where('language_code',$row->code)->first();
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
        // echo json_encode($data);exit;
            
            for ($i=0; $i < count($data['language_code']); $i++) { 
                if($data['language_code'][$i] == 'en'){
                    $updateData = array(
                        'title' => $data['title'][$i],
                        'description' => $data['description'][$i],
                    );
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'title' => $data['title'][$i],
                    'description' => $data['description'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                CmsContentTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
