<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchLog extends Model
{
    use HasFactory;

    public function user(){
        return $this->hasOne('App\Models\User',"id","user_id");
    }

    /**
    * Fetch list of user from here
    **/
    public static function getLists($search){
        try 
        {
            $finalArr = array();
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            if(isset($search['keyword']) && !empty($search['keyword']))
            {
                $obj = $obj->where('keyword', 'like', '%'.trim($search['keyword']).'%');
            }    
            $data = $obj->latest('created_at')->with('user')->groupBy('keyword')->get();
            if(count($data)){
                foreach($data as $row){
                    $row->count = SearchLog::where('keyword',$row   ->keyword)->count();
                    array_push($finalArr,$row);
                }   
            }
            $keys = array_column($finalArr, 'count');
            array_multisort($keys, SORT_DESC, $finalArr);
            return $finalArr;
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
