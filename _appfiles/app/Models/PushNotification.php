<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    use HasFactory;

    /**
     * Fetch list of categories from here
    **/
    public static function getLists($search){
        try {
            $obj = new self;
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            $data = $obj->latest('created_at')->paginate($pagination)->appends('perpage', $pagination);
            if(count($data)){
                foreach($data as $row){
                    $userNamesArr = array();
                    if($row->user_id!=''){
                        $userIds = explode(',',$row->user_id);
                        $users = User::whereIn('id',$userIds)->get();
                        if(count($users)){
                            foreach($users as $users_data){
                                array_push($userNamesArr,$users_data->name);
                            }
                        }
                        if(count($userNamesArr)){
                            $row->user_names = implode(',',$userNamesArr);
                        }
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
     * Delete particular resource
    **/
    public static function deleteRecord($id) 
    {
        try 
        {
            $obj = new self;    
            $obj->where('id',$id)->delete();   
            return ['status' => true, 'message' => config('constant.common.messages.success_delete')];
        }
        catch (\Exception $e) 
        {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
