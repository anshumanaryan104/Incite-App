<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "quotes";

    /**
     * Fetch list  from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['quote']) && !empty($search['quote']))
            {
                $obj = $obj->where('quote', 'like', '%'.trim($search['quote']).'%');
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
                $data['created_at'] = date('Y-m-d H:i:s');
                $entry_id = $obj->insertGetId($data);
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'quote_id'=>$entry_id,
                        'language_code'=>$language->code,
                        'quote'=>$data['quote'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    QuoteTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $newsTranslation = [
                    'quote'=>$data['quote'],
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = QuoteTranslation::where(['quote_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $newsTranslation['updated_at'] = date('Y-m-d H:i:s');
                    QuoteTranslation::where('id',$translationData->id)->update($newsTranslation);
                }
                else
                {
                    $newsTranslation['quote_id'] = $id;
                    $newsTranslation['language_code'] = $language_code;
                    $newsTranslation['created_at'] = date('Y-m-d H:i:s');
                    QuoteTranslation::insert($newsTranslation);
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
            QuoteTranslation::where('live_news_id',$id)->delete();
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
                    $row->details = QuoteTranslation::where('quote_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'quote_id' => $id,
                            'language_code' => $row->code,
                            'quote' => $rowObj->quote,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        QuoteTranslation::insert($postData);
                        $row->details = QuoteTranslation::where('live_news_id',$id)->where('language_code',$row->code)->first();
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
                        'quote' => $data['quote'][$i],
                    );
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'quote' => $data['quote'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                QuoteTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
