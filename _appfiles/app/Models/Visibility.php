<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visibility extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "visibilities";

    /**
     * Fetch list  from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['display_name']) && !empty($search['display_name']))
            {
                $obj = $obj->where('display_name', 'like', '%'.trim($search['display_name']).'%');
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
     * Fetch particular detail
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
     * Add or update
    **/
    public static function addUpdate($data, $id=0)
    {
        try 
        {
    		$obj = new self;
            unset($data['_token']);
            if($id==0)
            {
                if (isset($data['is_website'])) {
                    if($data['is_website']=='on'){
                        $data['is_website'] = 1;
                    }else{
                        $data['is_website'] = $data['is_website'];
                    }
                }else{
                    $data['is_website'] = 0;
                }
                if (isset($data['is_app'])) {
                    if($data['is_app']=='on'){
                        $data['is_app'] = 1;
                    }else{
                        $data['is_app'] = $data['is_app'];
                    }
                }else{
                    $data['is_app'] = 0;
                }
                $data['created_at'] = date('Y-m-d H:i:s');
                $entry_id = $obj->insertGetId($data);
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'visibility_id'=>$entry_id,
                        'language_code'=>$language->code,
                        'display_name'=>$data['display_name'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    VisibilityTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                if (isset($data['is_website'])) {
                    if($data['is_website']=='on'){
                        $data['is_website'] = 1;
                    }else{
                        $data['is_website'] = $data['is_website'];
                    }
                }else{
                    $data['is_website'] = 0;
                }
                if (isset($data['is_app'])) {
                    if($data['is_app']=='on'){
                        $data['is_app'] = 1;
                    }else{
                        $data['is_app'] = $data['is_app'];
                    }
                }else{
                    $data['is_app'] = 0;
                }
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $newsTranslation = [
                    'display_name'=>$data['display_name'],
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = VisibilityTranslation::where(['visibility_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $newsTranslation['updated_at'] = date('Y-m-d H:i:s');
                    VisibilityTranslation::where('id',$translationData->id)->update($newsTranslation);
                }
                else
                {
                    $newsTranslation['visibility_id'] = $id;
                    $newsTranslation['language_code'] = $language_code;
                    $newsTranslation['created_at'] = date('Y-m-d H:i:s');
                    VisibilityTranslation::insert($newsTranslation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_update')];
            }
        } 
        catch (\Exception $e) 
        {
    		return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
    	}
    }

    /**
     * Delete particular entry
    **/
    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            VisibilityTranslation::where('visibility_id',$id)->delete();
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
     * Fetch languages data of particular detail
    **/
    public static function getTranslation($id){
        try {
            $rowObj = new self;
            $rowObj = $rowObj->where('id',$id)->first();
            $data = Language::where('status',1)->get();
            if($rowObj){
                foreach ($data as $row) {
                    $row->details = VisibilityTranslation::where('visibility_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'visibility_id' => $id,
                            'language_code' => $row->code,
                            'display_name' => $rowObj->display_name,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        VisibilityTranslation::insert($postData);
                        $row->details = VisibilityTranslation::where('live_news_id',$id)->where('language_code',$row->code)->first();
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
     * Add or update translation
    **/
    public static function updateTranslation($data,$id=0) {
        try {
            $obj = new self;
            for ($i=0; $i < count($data['language_code']); $i++) { 
                if($data['language_code'][$i] == 'en'){
                    $updateData = array(
                        'display_name' => $data['display_name'][$i],
                    );
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'display_name' => $data['display_name'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                VisibilityTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
