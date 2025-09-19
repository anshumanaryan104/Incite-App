<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;   
use Illuminate\Support\Facades\Session;
use DB;
use Kyslik\ColumnSortable\Sortable;

class Blog extends Model
{
    use HasFactory;
    use Sortable;

    public $sortable = [
        'title',
        'schedule_date',
    ];

    public function images(){
        return $this->hasMany('App\Models\BlogImage',"blog_id","id");
    }
    public function image(){
        return $this->hasOne('App\Models\BlogImage',"blog_id","id");
    }
    public function blog_visibility(){
        return $this->hasMany('App\Models\BlogVisibility',"blog_id","id")->with('visibility');
    }
    public function blog_category(){
        return $this->hasMany('App\Models\BlogCategory',"blog_id","id")->where('type','category')->with('category');
    }
    public function blog_sub_category(){
        return $this->hasMany('App\Models\BlogCategory',"blog_id","id")->where('type','subcategory')->with('category');
    }

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

            if(isset($search['category_id']) && $search['category_id'] != '')
            {
                $blogCat = BlogCategory::where('category_id',$search['category_id'])->get();
                if(count($blogCat)){
                    foreach($blogCat as $blogCat_data){
                        if(!in_array($blogCat_data->blog_id,$blogIdArr)){
                            array_push($blogIdArr,$blogCat_data->blog_id);
                        }
                    }
                }
            }   
            if(isset($search['visibility_id']) && $search['visibility_id'] != '')
            {
                if($search['visibility_id']==0){
                    $obj = $obj->where('is_featured',1);
                }else{
                    $blogVisibility = BlogVisibility::where('visibility_id',$search['visibility_id'])->get();
                    if(count($blogVisibility)){
                        foreach($blogVisibility as $blogVisibility_data){
                            if(!in_array($blogVisibility_data->blog_id,$blogIdArr)){
                                array_push($blogIdArr,$blogVisibility_data->blog_id);
                            }
                        }
                    }
                }
            }   
            if(isset($search['status']) && $search['status']!='')
            {
                $obj = $obj->where('status',$search['status']);
            }

            if((isset($search['from_date']) && $search['from_date']!='') && (isset($search['to_date']) && $search['to_date']!=''))
            {
                $obj = $obj->whereBetween('schedule_date', [$search['from_date'], $search['to_date']]);
            	// $search['to_date'] = date("Y-m-d h:i:s",strtotime($search['to_date']." 23:59:59"));
                // $contact = $contact->where('created_at','>=',$search['from_date'])->where('created_at','<=',$search['to_date']);
            }
            else if(isset($search['from_date']) && $search['from_date']!=''){
                $obj = $obj->where('schedule_date','>=',$search['from_date']);
            }
            else if(isset($search['to_date']) &&  $search['to_date']!=''){
                // echo "there";exit;
                $obj = $obj->where('schedule_date','<=',$search['to_date']);
            }

            // if(isset($search['date']) && $search['date']!='')
            // {
            //     $dates = explode(' to ',$search['date']);
            //     $startDate = $dates[0];
            //     $endDate = $dates[1];
            //     $obj = $obj->whereBetween('created_at', [$startDate, $endDate]);
            // }

            if(isset($search['type']) && !empty($search['type']))
            {
                $obj = $obj->where('type',trim($search['type']));
            } 
            if(count($blogIdArr)>0){
                $obj = $obj->whereIn('id',$blogIdArr);
            }
            $data = $obj->with('image')->with('blog_visibility')->sortable(['schedule_date'=>'DESC'])->paginate($pagination)->appends('perpage', $pagination);
            // latest('schedule_date')->
            if(count($data)){
                foreach($data as $row)
                {
                    $row->view_count = BlogAnalytic::where('type','view')->where('blog_id',$row->id)->count();
                    $row->blog_categories = BlogCategory::where('blog_id',$row->id)->get();
                    $row->category_names = "";
                    $category_id_array = array();
                    if(count($row->blog_categories)){
                        foreach($row->blog_categories as $categories){
                            $category_data = Category::where('id',$categories->category_id)->first();
                            if($category_data){
                                array_push($category_id_array,$category_data->name);
                            }
                        }
                        if(count($category_id_array)){
                            $row->category_names = implode(",",$category_id_array);
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
     * Add or update data
    **/
    public static function addUpdate($data, $id=0)
    {
    	try {
            $obj = new self;
            unset($data['_token']);
            // echo json_encode($data);exit;
            if($id==0)
            {                
                $category_id = "";
                $sub_category_id = "";
                $visibillity = "";
                $question = "";
                $image = "";
                $option = "";
                $button_name = "";
                $data['accent_code'] = setting('blog_accent');
                $data['voice'] = setting('blog_voice');
                if(isset($data['category_id']) && $data['category_id']!=''){
                    $category_id = $data['category_id'];
                    unset($data['category_id']);
                }
                if(isset($data['sub_category_id']) && $data['sub_category_id']!=''){
                    $sub_category_id = $data['sub_category_id'];
                    unset($data['sub_category_id']);
                }else{
                    unset($data['sub_category_id']);
                }
                if(isset($data['visibillity']) && $data['visibillity']!=''){
                    $visibillity = $data['visibillity'];
                    unset($data['visibillity']);
                }else{
                    unset($data['visibillity']);
                }
                if(isset($data['question']) && $data['question']!=''){
                    $question = $data['question'];
                    unset($data['question']);
                }else{
                    unset($data['question']); 
                }
                if(isset($data['option']) && $data['option']!=''){
                    $option = $data['option'];
                    unset($data['option']);
                }else{
                    unset($data['option']);
                }
                if(isset($data['image']) && $data['image']!=''){
                    $image = $data['image'];
                    unset($data['image']);
                }       
                if(isset($data['button_name']) && $data['button_name']!=''){
                    $button_name = $data['button_name'];
                    if($button_name=='Draft'){
                        $data['status'] = 2;
                    }else if($button_name=='Submit'){
                        $data['status'] = 3;
                    }else if($button_name=='Publish'){
                        $data['status'] = 1;
                    }
                    unset($data['button_name']);
                }else{
                    unset($data['button_name']);
                } 
                if(isset($data['schedule_date']) && $data['schedule_date']!=''){
                    if(date("Y-m-d H:i:s",strtotime($data['schedule_date'])) > date("Y-m-d H:i:s")){
                        $data['status'] = 4;
                        $data['schedule_date'] = date("Y-m-d H:i:s",strtotime($data['schedule_date']));
                    }
                }else{
                    $data['schedule_date'] = date("Y-m-d H:i:s");
                }     
                if(isset($data['is_featured']) && $data['is_featured']=='on'){
                    $data['is_featured'] = 1;
                }   
                if(isset($data['is_voting_enable']) && $data['is_voting_enable']=='on'){
                    $data['is_voting_enable'] = 1;
                }    
                $data['created_by'] = Auth::user()->id;
                $data['created_at'] = date('Y-m-d H:i:s'); 
                $slug = \Helpers::createSlug($data['title'],'blog',$id,false);
                $data['slug'] = $slug;
                $entry_id = $obj->insertGetId($data);
                if($entry_id){
                    $image = BlogImage::where('session_id',Session::get('session_id'))->get();
                    if(isset($image) && $image!=''){
                        foreach($image as $image_data){                            
                            $image_arr = array(
                                'session_id'=>null,
                                'blog_id'=>$entry_id,
                                'updated_at'=>date("Y-m-d H:i:s")
                            );
                            BlogImage::where('id',$image_data->id)->update($image_arr);
                        }
                    }
                    if(isset($category_id) && $category_id!=''){
                        foreach($category_id as $category_id_data){
                            $cat_arr = array(
                                'category_id'=>$category_id_data,
                                'blog_id'=>$entry_id,
                                'type'=>'category',
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            BlogCategory::insert($cat_arr);
                        }
                    }
                    if(isset($sub_category_id) && $sub_category_id!=''){
                        foreach($sub_category_id as $sub_category_id_data){                            
                            $sub_cat_arr = array(
                                'category_id'=>$sub_category_id_data,
                                'blog_id'=>$entry_id,
                                'type'=>'subcategory',
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            BlogCategory::insert($sub_cat_arr);
                        }
                    }
                    if(isset($visibillity) && $visibillity!=''){
                        foreach($visibillity as $visibillity_data){
                            $visibillity_arr = array(
                                'visibility_id'=>$visibillity_data,
                                'blog_id'=>$entry_id,
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            BlogVisibility::insert($visibillity_arr);
                        }
                    }
                    if(isset($question) && $question!=''){
                        $question_arr = array(
                            'question'=>$question,
                            'blog_id'=>$entry_id,
                            'created_at'=>date("Y-m-d H:i:s")
                        );
                        $question_id = BlogQuestion::insertGetId($question_arr);
                        if($question_id){                            
                            if(isset($option) && count($option)>0){
                                foreach($option as $option_data){                                    
                                    $cat_arr = array(
                                        'option'=>$option_data,
                                        'blog_pool_question_id'=>$question_id,
                                        'created_at'=>date("Y-m-d H:i:s")
                                    );
                                    BlogQuestionOption::insertGetId($cat_arr);
                                }
                            }
                        }
                    }                   
                }  

                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'blog_id' =>$entry_id,
                        'language_code' =>$language->code,
                        'title' =>$data['title'],
                        'tags' =>$data['tags'],
                        'description' =>$data['description'],
                        'seo_title' =>$data['seo_title'],
                        'seo_keyword' =>$data['seo_keyword'],
                        'seo_tag' =>$data['seo_tag'],
                        'seo_description' =>$data['seo_description'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    BlogTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                $category_id = "";
                $question_id = "";
                $visibillity = "";
                $question = "";
                $image = "";
                $option = "";
                $button_name = "";
                if(isset($data['category_id']) && $data['category_id']!=''){
                    $category_id = $data['category_id'];
                    unset($data['category_id']);
                }
                if(isset($data['sub_category_id']) && $data['sub_category_id']!=''){
                    $sub_category_id = $data['sub_category_id'];
                    unset($data['sub_category_id']);
                }else{
                    unset($data['sub_category_id']);
                }
                if(isset($data['visibillity']) && $data['visibillity']!=''){
                    $visibillity = $data['visibillity'];
                    unset($data['visibillity']);
                }else{
                    unset($data['visibillity']);
                }
                if(isset($data['question']) && $data['question']!=''){
                    $question = $data['question'];
                    $question_id = $data['question_id'];
                    unset($data['question']);
                    unset($data['question_id']);
                }else{
                    unset($data['question']); 
                    unset($data['question_id']);
                }
                if(isset($data['option']) && $data['option']!=''){
                    $option = $data['option'];
                    unset($data['option']);
                }else{
                    unset($data['option']);
                }
                if(isset($data['option_id']) && $data['option_id']!=''){
                    $option_id = $data['option_id'];
                    unset($data['option_id']);
                }else{
                    unset($data['option_id']);
                }
                if(isset($data['image']) && $data['image']!=''){
                    $image = $data['image'];
                    unset($data['image']);
                }       
                if(isset($data['button_name']) && $data['button_name']!=''){
                    $button_name = $data['button_name'];
                    if($button_name=='Submit'){
                        $data['status'] = 3;
                    }else if($button_name=='Publish'){
                        $data['status'] = 1;
                    }
                    unset($data['button_name']);
                }else{
                    unset($data['button_name']);
                } 
                if(isset($data['schedule_date']) && $data['schedule_date']!=''){
                    if(date("Y-m-d H:i:s",strtotime($data['schedule_date'])) > date("Y-m-d H:i:s")){
                        $data['status'] = 4;
                        $data['schedule_date'] = date("Y-m-d H:i:s",strtotime($data['schedule_date']));
                    }
                }else{
                    $data['schedule_date'] = date("Y-m-d H:i:s");
                }  
                if(isset($data['is_featured']) && $data['is_featured']=='on'){
                    $data['is_featured'] = 1;
                }else{
                    $data['is_featured'] = 0;
                }   
                if(isset($data['is_voting_enable']) && $data['is_voting_enable']=='on'){
                    $data['is_voting_enable'] = 1;
                }else{
                    $data['is_voting_enable'] = 0;
                } 
                if(isset($data['created_at']) && $data['created_at']!=''){
                    $data['created_at'] = date("Y-m-d H:i:s",strtotime($data['created_at']));
                } 
                $obj->where('id',$id)->update($data);
                if($id){
                    if(isset($sub_category_id) && $sub_category_id!=''){
                        foreach($sub_category_id as $sub_category_id_data){
                            $sub_category = BlogCategory::where('category_id',$sub_category_id_data)->where('blog_id',$id)->first();
                            if(!$sub_category){
                                $cat_arr = array(
                                    'category_id'=>$sub_category_id_data,
                                    'blog_id'=>$id,
                                    'type'=>'subcategory',
                                    'created_at'=>date("Y-m-d H:i:s")
                                );
                                BlogCategory::insert($cat_arr);
                            }
                        }
                    }
                    if(isset($category_id) && $category_id!=''){
                        foreach($category_id as $category_id_data){
                            $category = BlogCategory::where('category_id',$category_id_data)->where('blog_id',$id)->first();
                            if(!$category){
                                $cat_arr = array(
                                    'category_id'=>$category_id_data,
                                    'blog_id'=>$id,
                                    'type'=>'category',
                                    'created_at'=>date("Y-m-d H:i:s")
                                );
                                BlogCategory::insert($cat_arr);
                            }
                        }
                    }
                    if(isset($visibillity) && $visibillity!=''){
                        foreach($visibillity as $visibillity_data){
                            $visibillity_detail = BlogVisibility::where('visibility_id',$visibillity_data)->where('blog_id',$id)->first();
                            if(!$visibillity_detail){
                                $visibillity_arr = array(
                                    'visibility_id'=>$visibillity_data,
                                    'blog_id'=>$id,
                                    'created_at'=>date("Y-m-d H:i:s")
                                );
                                BlogVisibility::insert($visibillity_arr);
                            }
                        }
                    }
                    
                    if(isset($question) && $question!=''){
                        $check_question = BlogQuestion::where('id',$question_id)->where('blog_id',$id)->first();
                        if(!$check_question){
                            $question_arr = array(
                                'question'=>$question,
                                'blog_id'=>$id,
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            $question_id = BlogQuestion::insertGetId($question_arr);
                            if($question_id){
                                if(isset($option) && count($option)>0){
                                    for($i=0;$i<count($option);$i++){
                                        if(isset($option_id) && count($option_id)>0){
                                            $check_question = BlogQuestionOption::where('id',$option_id[$i])->where('blog_id',$id)->first();
                                            if(!$check_question){
                                                $cat_arr = array(
                                                    'option'=>$option[$i],
                                                    'blog_pool_question_id'=>$question_id,
                                                    'created_at'=>date("Y-m-d H:i:s")
                                                );
                                                BlogQuestionOption::insert($cat_arr);
                                            }else{
                                                BlogQuestionOption::where('id',$option_id[$i])->update(['option'=>$option[$i]]);
                                            }
                                        }else{
                                            $cat_arr = array(
                                                'option'=>$option[$i],
                                                'blog_pool_question_id'=>$question_id,
                                                'created_at'=>date("Y-m-d H:i:s")
                                            );
                                            BlogQuestionOption::insert($cat_arr);
                                        }                                        
                                    }                                    
                                }
                            }
                        }else{
                            BlogQuestion::where('id',$question_id)->update(['question'=>$question]);
                            
                            if(isset($option) && count($option)>0){
                                
                                for($j=0;$j<count($option);$j++){
                                    
                                    if(isset($option_id[$j]) && $option_id[$j]!=''){
                                        // echo json_encode($option_id[$j]);exit;
                                        $check_question_option = BlogQuestionOption::where('id',$option_id[$j])->first();
                                        // echo json_encode($check_question_option);exit;
                                        if(!$check_question_option){
                                            $cat_arr = array(
                                                'option'=>$option[$j],
                                                'blog_pool_question_id'=>$question_id,
                                                'created_at'=>date("Y-m-d H:i:s")
                                            );
                                            BlogQuestionOption::insert($cat_arr);
                                        }else{
                                            BlogQuestionOption::where('id',$check_question_option->id)->update(['option'=>$option[$j]]);
                                        }
                                    }else{
                                        $cat_arr = array(
                                            'option'=>$option[$j],
                                            'blog_pool_question_id'=>$question_id,
                                            'created_at'=>date("Y-m-d H:i:s")
                                        );
                                        BlogQuestionOption::insert($cat_arr);
                                    }                                        
                                }                                    
                            }
                        }
                    }
                }             
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translate = BlogTranslation::where('language_code',$language->code)->where('blog_id',$id)->first();
                    $translation = array(
                        'blog_id' =>$id,
                        'language_code' =>$language->code,
                        'title' =>$data['title'],
                        'tags' =>$data['tags'],
                        'description' =>$data['description'],
                        'seo_title' =>$data['seo_title'],
                        'seo_keyword' =>$data['seo_keyword'],
                        'seo_tag' =>$data['seo_tag'],
                        'seo_description' =>$data['seo_description'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    if($translate){
                        $translation['updated_at'] = date("Y-m-d H:i:s");
                        BlogTranslation::where('id',$translate->id)->update($translation);
                    }else{
                        $translation['created_at'] = date("Y-m-d H:i:s");
                        BlogTranslation::insert($translation);
                    }
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
     * Add or update data
    **/
    public static function addUpdateQuote($data, $id=0)
    {
    	try {
            $obj = new self;
            unset($data['_token']);
            $category_id = "";
            $sub_category_id = "";
            if(isset($data['category_id']) && $data['category_id']!=''){
                $category_id = $data['category_id'];
                unset($data['category_id']);
            } 
            if(isset($data['sub_category_id']) && $data['sub_category_id']!=''){
                $sub_category_id = $data['sub_category_id'];
                unset($data['sub_category_id']);
            }else{
                unset($data['sub_category_id']);
            }   
            if(isset($data['background_image']) && $data['background_image']!=''){
                $uploadImage = \Helpers::uploadFiles($data['background_image'],'blog/');
                if($uploadImage['status']==true){
                    $data['background_image'] = $uploadImage['file_name'];
                }
            }
            if(isset($data['button_name']) && $data['button_name']!=''){
                $button_name = $data['button_name'];
                if($button_name=='Submit'){
                    $data['status'] = 3;
                }else if($button_name=='Publish'){
                    $data['status'] = 1;
                }
                unset($data['button_name']);
            }else{
                unset($data['button_name']);
            } 
            if(isset($data['schedule_date']) && $data['schedule_date']!=''){
                $data['schedule_date'] = date("Y-m-d H:i:s",strtotime($data['schedule_date']));
            }else{
                $data['schedule_date'] = date("Y-m-d H:i:s");
            }  
            if($id==0)
            {     
                $data['accent_code'] = setting('blog_accent');
                $data['voice'] = setting('blog_voice');
                $data['type'] = "quote";
                $data['created_by'] = Auth::user()->id;
                $data['created_at'] = date('Y-m-d H:i:s'); 
                $slug = \Helpers::createSlug($data['title'],'blog',$id,false);
                $data['slug'] = $slug;
                
                $entry_id = $obj->insertGetId($data);
                if($entry_id){
                    if(isset($category_id) && $category_id!=''){
                        foreach($category_id as $category_id_data){
                            $cat_arr = array(
                                'category_id'=>$category_id_data,
                                'blog_id'=>$entry_id,
                                'type'=>'category',
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            BlogCategory::insert($cat_arr);
                        }
                    }
                    if(isset($sub_category_id) && $sub_category_id!=''){
                        foreach($sub_category_id as $sub_category_id_data){                            
                            $sub_cat_arr = array(
                                'category_id'=>$sub_category_id_data,
                                'blog_id'=>$entry_id,
                                'type'=>'subcategory',
                                'created_at'=>date("Y-m-d H:i:s")
                            );
                            BlogCategory::insert($sub_cat_arr);
                        }
                    }
                }              
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translation = array(
                        'blog_id' =>$entry_id,
                        'language_code' =>$language->code,
                        'title' =>$data['title'],
                        'description' =>$data['description'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    BlogTranslation::insert($translation);
                }
                return ['status' => true, 'message' => config('constant.common.messages.success_add')];
            }
            else
            {
                if(isset($data['created_at']) && $data['created_at']!=''){
                    $data['created_at'] = date("Y-m-d H:i:s",strtotime($data['created_at']));
                } 
                $obj->where('id',$id)->update($data);
                if($id){
                    if(isset($category_id) && $category_id!=''){
                        foreach($category_id as $category_id_data){
                            $category = BlogCategory::where('category_id',$category_id_data)->where('blog_id',$id)->first();
                            if(!$category){
                                $cat_arr = array(
                                    'category_id'=>$category_id_data,
                                    'blog_id'=>$id,
                                    'type'=>'category',
                                    'created_at'=>date("Y-m-d H:i:s")
                                );
                                BlogCategory::insert($cat_arr);
                            }
                        }
                    }
                    if(isset($sub_category_id) && $sub_category_id!=''){
                        foreach($sub_category_id as $sub_category_id_data){
                            $sub_category = BlogCategory::where('category_id',$sub_category_id_data)->where('blog_id',$id)->first();
                            if(!$sub_category){
                                $cat_arr = array(
                                    'category_id'=>$sub_category_id_data,
                                    'blog_id'=>$id,
                                    'type'=>'subcategory',
                                    'created_at'=>date("Y-m-d H:i:s")
                                );
                                BlogCategory::insert($cat_arr);
                            }
                        }
                    }
                }               
                $languages = Language::where('status',1)->get();
                foreach ($languages as $language) 
                {
                    $translate = BlogTranslation::where('language_code',$language->code)->where('blog_id',$id)->first();
                    $translation = array(
                        'blog_id' =>$id,
                        'language_code' =>$language->code,
                        'title' =>$data['title'],
                        'description' =>$data['description'],
                        'created_at' =>date("Y-m-d H:i:s"),
                    );
                    if($translate){
                        $translation['updated_at'] = date("Y-m-d H:i:s");
                        BlogTranslation::where('id',$translate->id)->update($translation);
                    }else{
                        $translation['created_at'] = date("Y-m-d H:i:s");
                        BlogTranslation::insert($translation);
                    }
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
     * Fetch particular detail
    **/
    public static function getDetail($id)
    {
        try 
        {
            $obj = new self;
            $data = $obj->where('id',$id)->with('images')->firstOrFail();
            $categoryArr = array();
            $subcategoryArr = array();
            $visibilityArr = array();
            $optionIdArr = array();
            $optionArr = array();

            if($data){
                $category = BlogCategory::where('blog_id',$id)->where('type','category')->get();
                // echo json_encode($category);exit;
                if(count($category)){
                    foreach($category as $category_data){
                        array_push($categoryArr,$category_data->category_id);
                    }
                }
                $subcategory = BlogCategory::where('blog_id',$id)->where('type','subcategory')->get();
                // echo json_encode($subcategory);exit;
                if(count($subcategory)){
                    foreach($subcategory as $subcategory_data){
                        array_push($subcategoryArr,$subcategory_data->category_id);
                    }
                }
                $visibility = BlogVisibility::where('blog_id',$id)->get();
                
                if(count($visibility)){
                    foreach($visibility as $visibility_data){
                        array_push($visibilityArr,$visibility_data->visibility_id);
                    }
                }
                $question = BlogQuestion::where('blog_id',$id)->first();
                if($question){
                    $data->question = $question->question;
                    $data->question_id = $question->id;
                    $option = BlogQuestionOption::where('blog_pool_question_id',$data->question_id)->get();
                    if(count($option)){
                        foreach($option as $option_data){
                            array_push($optionIdArr,$option_data->id);
                            array_push($optionArr,$option_data);
                        }
                    }
                }
                $data->categoryArr = $categoryArr;
                $data->subcategoryArr = $subcategoryArr;
                $data->optionArr = $optionArr;
                $data->visibilityArr = $visibilityArr;
                $data->optionIdArr = $optionIdArr;
                
            }
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

    /**
     * Fetch list of most viewed blogs
    **/
    public static function getMostViewedBlogs(){
        try {
            $obj = new self;
            $blogIdArr = array();

            $popularBlogs = BlogAnalytic::where('type','view')->select('blog_id', DB::raw('COUNT(*) as count'))->groupBy('blog_id')->orderByDesc('count')->limit(5)->get();
            if(count($popularBlogs)){
                foreach($popularBlogs as $popularBlogs_data){
                    if(!in_array($popularBlogs_data->blog_id,$blogIdArr)){
                        array_push($blogIdArr,$popularBlogs_data->blog_id);
                    }
                }
            }
            $data = $obj->whereIn('id',$blogIdArr)->latest('created_at')->with('image')->with('blog_visibility')->get();
            if(count($data)){
                foreach($data as $row)
                {
                    $row->view_count = BlogAnalytic::where('type','view')->where('blog_id',$row->id)->count();
                    $row->blog_categories = BlogCategory::where('blog_id',$row->id)->get();
                    $row->category_names = "";
                    $category_id_array = array();
                    if(count($row->blog_categories)){
                        foreach($row->blog_categories as $categories){
                            $category_data = Category::where('id',$categories->category_id)->first();
                            if($category_data){
                                array_push($category_id_array,$category_data->name);
                            }
                        }
                        if(count($category_id_array)){
                            $row->category_names = implode(",",$category_id_array);
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
}
