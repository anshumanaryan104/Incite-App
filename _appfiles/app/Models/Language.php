<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\File;
use DB;

class Language extends Model
{
    use HasFactory;

    use SoftDeletes;
    protected $table = "languages";

    /**
     * Fetch list  from here
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
            // echo json_encode($data);exit;
            if($id==0)
            {
                $code = DB::table('language_codes')->where('id',$data['code_id'])->first();
                if($code){
                    $data['name'] = $code->name;
                    $data['code'] = $code->code;
                }
                $data['created_at'] = date('Y-m-d H:i:s');
                if(isset($data['is_default']) && $data['is_default']=='on'){
                    $data['is_default'] = 1; 
                }   
                $entry_id = $obj->insertGetId($data);
                if($entry_id){
                    if(isset($data['is_default']) && $data['is_default']==1){
                        Language::where('id','!=',$entry_id)->update(['is_default'=>0]); 
                    }
                }
                $fileName = 'lang.php';
                $path = resource_path('/lang/'.$data['code']); // Specify the path where you want to create the folder
                $filePath = $path . '/'.$fileName;
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                if (!file_exists($filePath)) {
                    file_put_contents($filePath, '<?php return [];');
                }
                $language = Language::where('code','en')->first();
                if ($language) {
                    $detail = "<?php
                        return [
                            /*
                            |--------------------------------------------------------------------------
                            | Pagination Language Lines
                            |--------------------------------------------------------------------------
                            |
                            | The following language lines are used by the paginator library to build
                            | the simple pagination links. You are free to change them to anything
                            | you want to customize your views to better match your application.
                            |
                            */
                    ";
                    $translations = Translation::where('language_id',$language->id)->get();
                    foreach ($translations as $row) {
                        $translationArr = array(
                            'language_id'=>$entry_id,
                            'group'=>$row->group,
                            'keyword'=>$row->keyword,
                            'key'=>$row->key,
                            'value'=>$row->value,
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                        Translation::insert($translationArr);
                        $row->value = str_replace("'", "",$row->value);
                        $detail .= "'".$row->keyword."' => '".$row->value."',\n";
                    }
                    $detail .= "];";
                        
                    $fp = fopen('resources/lang/'.$data['code'].'/lang.php', 'w');
                    fwrite($fp, $detail);
                    fclose($fp);
                }
                return ['status' => true, 'message' => "Data added successfully."];
            }
            else
            {
                $code = DB::table('language_codes')->where('id',$data['code_id'])->first();
                if($code){
                    $data['name'] = $code->name;
                    $data['code'] = $code->code;
                }
                $data['updated_at'] = date('Y-m-d H:i:s');
                if(isset($data['is_default']) && $data['is_default']=='on'){
                    $data['is_default'] = 1;    
                    Language::where('id','!=',$id)->update(['is_default'=>0]); 
                }
                $obj->where('id',$id)->update($data);
                return ['status' => true, 'message' => "Data updated successfully."];
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
            $language = Language::where('id',$id)->first();   
            if($language){
                if($language->is_default==0){
                    $directoryPath = resource_path('lang/'.$language->code); // Replace 'path/to/your/folder' with the actual folder path you want to delete
                    File::deleteDirectory($directoryPath);
                    $obj->where('id',$id)->delete();   
                    Translation::where('language_id',$id)->delete(); 
                    return ['status' => true, 'message' => "Data deleted successfully."];
                }else{
                    return ['status' => false, 'message' => "You have to first set any language default then you can delete this language."];
                }                   
            }else{
                return ['status' => false, 'message' => "Data not found."];
            }            
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
}
