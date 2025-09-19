<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as Roles;
use Spatie\Permission\Models\Permission;

class Role extends Model
{
    use HasFactory;
    protected $table = "roles";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_name','status'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
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
                $role = Roles::create(['name' => $data['name']]);
                $role->syncPermissions($data['permission']);
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                $role = Roles::find($id);
                $role->name = $data['name'];
                $role->save();
                $role->syncPermissions($data['permission']);
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
            Roles::where('id',$id)->delete();   
            return ['status' => true, 'message' => "Data deleted successfully."];
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
