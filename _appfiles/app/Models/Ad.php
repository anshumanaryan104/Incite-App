<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ad extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "ads";

    /**
     * Fetch list of categories from here
    **/
    public static function getLists($search){
        try {
            $obj = new self;
            $blogIdArr = array();
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['title']) && !empty($search['title']))
            {
                $obj = $obj->where('title', 'like', '%'.trim($search['title']).'%');
            }   
            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }
            $data = $obj->orderBy('order','ASC')->paginate($pagination)->appends('perpage', $pagination);
            return $data;
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
    /**
     * Add or update data
    **/
    public static function addUpdate($data, $id=0)
    {
        $obj = new self;
            // echo json_encode($data);
            unset($data['_token']);
            if(isset($data['media_type']) && $data['media_type']!=''){
                if($data['media_type']=='image'){
                    if(isset($data['image']) && $data['image']!='')
                    {                        
                        $uploadImage = \Helpers::uploadFiles($data['image'],'ad/');               
                        if($uploadImage['status']==true)
                        {
                            $data['media'] = $uploadImage['file_name'];
                        }
                    }     
                    unset($data['image']);
                    unset($data['image_url']);
                }else if($data['media_type']=='video'){
                    if(isset($data['media']) && $data['media']!='')
                    {                        
                        $uploadImage = \Helpers::uploadFiles($data['media'],'ad/video/');                
                        if($uploadImage['status']==true)
                        {
                            $data['media'] = $uploadImage['file_name'];
                        }
                    }
                    unset($data['image']);
                    unset($data['image_url']);
                }else{
                    unset($data['image']);
                    $data['media'] = null;
                }
            }            
            if(isset($data['start_date']) && $data['start_date']!=''){
                $data['start_date'] = date("Y-m-d h:i:s",strtotime($data['start_date']));
            }  
            if(isset($data['end_date']) && $data['end_date']!=''){
                $data['end_date'] = date("Y-m-d h:i:s",strtotime($data['end_date']));
            } 
            if($id==0){
                $data['status'] = 1;
                $data['created_at'] = date('Y-m-d H:i:s'); 
                $entry_id = $obj->insertGetId($data);
                return ['status' => true, 'message' => "Data added successfully."];
            }
            else
            {
                $obj->where('id',$id)->update($data);
                return ['status' => true, 'message' => "Data updated successfully."];
            }
    	// try {
        //     $obj = new self;
        //     unset($data['_token']);
        //     if($id==0)
        //     {
        //         $category_id = "";
        //         $visibillity = "";
        //         $question = "";
        //         $image = "";
        //         $option = "";

        //         if(isset($data['category_id']) && $data['category_id']!=''){
        //             $category_id = $data['category_id'];
        //             unset($data['category_id']);
        //         }
        //         if(isset($data['visibillity']) && $data['visibillity']!=''){
        //             $visibillity = $data['visibillity'];
        //             unset($data['visibillity']);
        //         }
        //         if(isset($data['question']) && $data['question']!=''){
        //             $question = $data['question'];
        //             unset($data['question']);
        //         }
        //         if(isset($data['option']) && $data['option']!=''){
        //             $option = $data['option'];
        //             unset($data['option']);
        //         }
        //         if(isset($data['image']) && $data['image']!=''){
        //             $image = $data['image'];
        //             unset($data['image']);
        //         }
        //         $data['created_at'] = date('Y-m-d H:i:s');
        //         // echo json_encode($data);exit;  
        //         $entry_id = $obj->insertGetId($data);
        //         // echo json_encode($e_paper_id);exit;                
        //         // $languages = Language::get();
        //         // foreach ($languages as $language) 
        //         // {
        //         //     $translation = [
        //         //         'e_paper_id'=>$e_paper_id,
        //         //         'language_code'=>$language->code,
        //         //         'name'=>$data['name'],
        //         //         'pdf'=>(isset($data['pdf']))?$data['pdf']:'',
        //         //         'created_at' =>date("Y-m-d H:i:s"),
        //         //     ];
        //         //     EpaperTranslation::insert($translation);
        //         // }
        //         return ['status' => true, 'message' => config('constant.common.messages.success_add')];
        //     }
        //     else
        //     {
        //         $data['updated_at'] = date('Y-m-d H:i:s');
        //         $obj->where('id',$id)->update($data);
        //         $epaperTranslation = [
        //             'name'=>$data['name'],
        //             'pdf'=>(isset($data['pdf']))?$data['pdf']:'',
        //         ];
        //         $language_code = \Helpers::returnUserLanguageCode();
        //         $translationData = EpaperTranslation::where(['e_paper_id'=>$id,'language_code'=>$language_code])->first();
        //         if($translationData)
        //         {
        //             $epaperTranslation['updated_at'] = date('Y-m-d H:i:s');
        //             EpaperTranslation::where('id',$translationData->id)->update($epaperTranslation);
        //         }
        //         else
        //         {
        //             $epaperTranslation['e_paper_id'] = $id;
        //             $epaperTranslation['language_code'] = $language_code;
        //             $epaperTranslation['created_at'] = date('Y-m-d H:i:s');
        //             EpaperTranslation::insert($epaperTranslation);
        //         }
        //         return ['status' => true, 'message' => config('constant.common.messages.success_update')];
        //     }
        // } 
        // catch (\Exception $e) 
        // {
    	// 	return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
    	// }
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
     * Delete particular epaper
    **/
    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            BlogTranslation::where('blog_id',$id)->delete();
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
                    $row->details = BlogTranslation::where('blog_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'blog_id' => $id,
                            'language_code' =>$row->code,
                            'title' =>$row->title,
                            'tags' =>$row->tags,
                            'description' =>$row->description,
                            'seo_title' =>$row->seo_title,
                            'seo_keyword' =>$row->seo_keyword,
                            'seo_tag' =>$row->seo_tag,
                            'seo_description' =>$row->seo_description,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        BlogTranslation::insert($postData);
                        $row->details = BlogTranslation::where('blog_id',$id)->where('language_code',$row->code)->first();
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
                        'title' =>$data['title'][$i],
                        'description' =>$data['description'][$i],
                    );
                    if(isset($data['tags'][$i]) && $data['tags'][$i]!=''){
                        $updateData['tags'] = $data['tags'][$i];
                    }
                    if(isset($data['seo_title'][$i]) && $data['seo_title'][$i]!=''){
                        $updateData['seo_title'] = $data['seo_title'][$i];
                    }
                    if(isset($data['seo_keyword'][$i]) && $data['seo_keyword'][$i]!=''){
                        $updateData['seo_keyword'] = $data['seo_keyword'][$i];
                    }
                    if(isset($data['seo_tag'][$i]) && $data['seo_tag'][$i]!=''){
                        $updateData['seo_tag'] = $data['seo_tag'][$i];
                    }
                    if(isset($data['seo_description'][$i]) && $data['seo_description'][$i]!=''){
                        $updateData['seo_description'] = $data['seo_description'][$i];
                    }
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'title' =>$data['title'][$i],
                    'description' =>$data['description'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                if(isset($data['tags'][$i]) && $data['tags'][$i]!=''){
                    $postData['tags'] = $data['tags'][$i];
                }
                if(isset($data['seo_title'][$i]) && $data['seo_title'][$i]!=''){
                    $postData['seo_title'] = $data['seo_title'][$i];
                }
                if(isset($data['seo_keyword'][$i]) && $data['seo_keyword'][$i]!=''){
                    $postData['seo_keyword'] = $data['seo_keyword'][$i];
                }
                if(isset($data['seo_tag'][$i]) && $data['seo_tag'][$i]!=''){
                    $postData['seo_tag'] = $data['seo_tag'][$i];
                }
                if(isset($data['seo_description'][$i]) && $data['seo_description'][$i]!=''){
                    $postData['seo_description'] = $data['seo_description'][$i];
                }
                BlogTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
