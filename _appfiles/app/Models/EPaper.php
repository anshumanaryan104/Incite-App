<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EPaper extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "e_papers";

    /**
     * Fetch list of categories from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['name']) && !empty($search['name']))
            {
                $obj = $obj->where('name', 'like', '%'.trim($search['name']).'%');
            } 

            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }
            $data = $obj->latest('created_at')->paginate($pagination)->appends('perpage', $pagination);
            // foreach ($data as $row) 
            // {
            //     $row->language_code = setting('preferred_site_language');
            //     $row->pdf = false;
            //     if (is_file(public_path().'/upload/e-paper/pdf/'.$row->pdf)) 
            //     {
            //         $row->pdf = url('/upload/e-paper/pdf/').'/'.$row->pdf;
            //     }
	        //     $val = EpaperTranslations::where('e_paper_id',$row->id)->where('language_code',setting('preferred_site_language'))->first();
            //     if ($val) 
            //     {
	        //         $row->paper_name = $val->paper_name;
            //         if (is_file(public_path().'/upload/e-paper/pdf/'.$val->pdf)) 
            //         {
	        //           $row->pdf = url('/upload/e-paper/pdf/').'/'.$val->pdf;
	        //         }
	        //     }
	        // }
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
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    /**
     * Add or update epaper
    **/
    public static function addUpdate($data, $id=0)
    {
    	try {
            $obj = new self;
            unset($data['_token']);
            if(isset($data['image']) && $data['image']!='')
            {
                $uploadImage = \Helpers::uploadFiles($data['image'],'e-paper/');
                if($uploadImage['status']==true)
                {
                    $data['image'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['pdf']) && $data['pdf']!='')
            {
                $uploadPdf = \Helpers::uploadPDF($data['pdf'],'e-paper/pdf/');
                if($uploadPdf['status']==true)
                {
                    $data['pdf'] = $uploadPdf['file_name'];
                }
            }
            if($id==0)
            {
                $data['created_at'] = date('Y-m-d H:i:s');
                // echo json_encode($data);exit;  
                $e_paper_id = $obj->insertGetId($data);
                // echo json_encode($e_paper_id);exit;                
                $languages = Language::get();
                foreach ($languages as $language) 
                {
                    $translation = [
                        'e_paper_id'=>$e_paper_id,
                        'language_code'=>$language->code,
                        'name'=>$data['name'],
                        'pdf'=>(isset($data['pdf']))?$data['pdf']:'',
                        'created_at' =>date("Y-m-d H:i:s"),
                    ];
                    EPaperTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                $data['updated_at'] = date('Y-m-d H:i:s');
                $obj->where('id',$id)->update($data);
                $epaperTranslation = [
                    'name'=>$data['name'],
                    'pdf'=>(isset($data['pdf']))?$data['pdf']:'',
                ];
                $language_code = \Helpers::returnUserLanguageCode();
                $translationData = EPaperTranslation::where(['e_paper_id'=>$id,'language_code'=>$language_code])->first();
                if($translationData)
                {
                    $epaperTranslation['updated_at'] = date('Y-m-d H:i:s');
                    EPaperTranslation::where('id',$translationData->id)->update($epaperTranslation);
                }
                else
                {
                    $epaperTranslation['e_paper_id'] = $id;
                    $epaperTranslation['language_code'] = $language_code;
                    $epaperTranslation['created_at'] = date('Y-m-d H:i:s');
                    EPaperTranslation::insert($epaperTranslation);
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
            EPaperTranslation::where('e_paper_id',$id)->delete();
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
                    $row->details = EPaperTranslation::where('e_paper_id',$id)->where('language_code',$row->code)->first();
                    if(!$row->details){
                        $postData = array(
                            'e_paper_id' => $id,
                            'language_code' => $row->code,
                            'name' => $rowObj->company_name,
                            'pdf' => $rowObj->pdf,
                            'created_at' => date("Y-m-d H:i:s")
                        );
                        EPaperTranslation::insert($postData);
                        $row->details = EPaperTranslation::where('e_paper_id',$id)->where('language_code',$row->code)->first();
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
                if(isset($data['pdf'][$i]) && $data['pdf'][$i]!='')
                {
                    $uploadPdf = \Helpers::uploadFiles($data['pdf'][$i],'e-paper/pdf/');
                    if($uploadPdf['status']==true)
                    {
                        $data['pdf'][$i] = $uploadPdf['file_name'];
                    }
                }
                if($data['language_code'][$i] == 'en'){
                    $updateData = array(
                        'name' => $data['name'][$i],
                    );
                    if(isset($data['pdf'][$i]) && $data['pdf'][$i]!=''){
                        $updateData['pdf'] = $data['pdf'][$i];
                    }
                    $obj->where('id',$id)->update($updateData);
                }
                $postData = array(
                    'language_code' => $data['language_code'][$i],
                    'name' => $data['name'][$i],
                    'updated_at' => date("Y-m-d H:i:s")
                );
                if(isset($data['pdf'][$i]) && $data['pdf'][$i]!=''){
                    $postData['pdf'] = $data['pdf'][$i];
                }
                EPaperTranslation::where('id',$data['translation_id'][$i])->update($postData);
            }
            return ['status' => true, 'message' => "Data updated successfully."];
        }
        catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
