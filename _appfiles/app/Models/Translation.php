<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\File;

class Translation extends Model
{
    use HasFactory;

    protected $table = "translations";

    public function language(){
        return $this->hasOne('App\Models\Language',"id","language_id");
    }

    /**
     * Fetch list  from here
    **/
    public static function getLists($search)
    {
        try 
        {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['value']) && !empty($search['value']))
            {
                $obj = $obj->where('keyword', 'like', '%'.trim($search['value']).'%');
            }   
            if(isset($search['group']) && $search['group']!='')
            {
                $obj = $obj->where('group',$search['group']);
            }
            if(isset($search['language_id']) && $search['language_id']!='')
            {
                $obj = $obj->where('language_id',$search['language_id']);
            }else{
                $obj = $obj->where('language_id',1);
            }
            $data = $obj->latest('created_at')->with('language')->paginate($pagination)->appends('perpage', $pagination);
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
            if($data){
                $data->translation = Translation::where('keyword',$data->keyword)->with('language')->get();
            }
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
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'language_id'=>$language->id,
                        'group'=>$data['group'],
                        'keyword'=>$data['keyword'],
                        'key'=>$data['keyword'],
                        'value'=>$data['value'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    Translation::insert($translation);
                    $fileName = 'lang.php';
                    $path = resource_path('/lang/'.$language->code); // Specify the path where you want to create the folder
                    $filePath = $path . '/'.$fileName;
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    if (!file_exists($filePath)) {
                        file_put_contents($filePath, '<?php return [];');
                    }
                }
                foreach ($languages as $lang) {
                    $data = "<?php
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
                    $translations = Translation::where('language_id',$lang->id)->get();
                    foreach ($translations as $row) {
                        $row->value = str_replace("'", "",$row->value);
                        $data .= "'".$row->keyword."' => '".$row->value."',\n";
                    }
                    $data .= "];";
                        
                    $fp = fopen('resources/lang/'.$lang->code.'/lang.php', 'w');
                    fwrite($fp, $data);
                    fclose($fp);
                }
                return ['status' => true, 'message' => "Data added successfully."];
            }
            else
            {
                if(count($data['value'])){
                    for ($i=0; $i < count($data['value']); $i++) {
                        Translation::where('id',$data['id'][$i])->update(['value'=>$data['value'][$i]]);
                    }
                }
                $languages = Language::where('status',1)->get();
                foreach ($languages as $lang) {
                    $data = "<?php
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
                    $translations = Translation::where('language_id',$lang->id)->get(); 
                    foreach ($translations as $row) {
                        $row->value = str_replace("'", "",$row->value);
                        $data .= "'".$row->keyword."' => '".$row->value."',\n";
                    }
                    $data .= "];";
                        
                    $fp = fopen('resources/lang/'.$lang->code.'/lang.php', 'w');
                    fwrite($fp, $data);
                    fclose($fp);
                }
                return ['status' => true, 'message' => "Data updated successfully."];
            }
        } 
        catch (\Exception $e) 
        {
    		return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
    	}
    }
}
