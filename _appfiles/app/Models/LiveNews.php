<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LiveNews extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "live_news";

    /**
     * Fetch list of live news from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['company_name']) && !empty($search['company_name']))
            {
                $obj = $obj->where('company_name', 'like', '%'.trim($search['company_name']).'%');
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
                $uploadImage = \Helpers::uploadFiles($data['image'],'live-news/');
                if($uploadImage['status']==true){
                    $data['image'] = $uploadImage['file_name'];
                }
            }
            if($id==0)
            {
                $data['created_at'] = date('Y-m-d H:i:s');
                $live_news_id = $obj->insertGetId($data);
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'live_news_id'=>$live_news_id,
                        'language_code'=>$language->code,
                        'company_name'=>$data['company_name'],
                        'url'=>$data['url'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    LiveNewsTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $newsTranslation = [
                    'company_name'=>$data['company_name'],
                    'url'=>$data['url'],
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = LiveNewsTranslation::where(['live_news_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $newsTranslation['updated_at'] = date('Y-m-d H:i:s');
                    LiveNewsTranslation::where('id',$translationData->id)->update($newsTranslation);
                }
                else
                {
                    $newsTranslation['live_news_id'] = $id;
                    $newsTranslation['language_code'] = $language_code;
                    $newsTranslation['created_at'] = date('Y-m-d H:i:s');
                    LiveNewsTranslation::insert($newsTranslation);
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
     * Delete particular epaper
    **/
    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            LiveNewsTranslation::where('live_news_id',$id)->delete();
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
                    $row->details = LiveNewsTranslation::where('live_news_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'live_news_id' => $id,
                            'language_code' => $row->code,
                            'company_name' => $rowObj->company_name,
                            'url' => $rowObj->url,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        LiveNewsTranslation::insert($postData);
                        $row->details = LiveNewsTranslation::where('live_news_id',$id)->where('language_code',$row->code)->first();
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
                        'company_name' => $data['company_name'][$i],
                        'url' => $data['url'][$i],
                    );
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'company_name' => $data['company_name'][$i],
                    'url' => $data['url'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                LiveNewsTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
