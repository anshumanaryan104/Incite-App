<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\Blog;
use App\Models\Language;
use App\Models\BlogTranslation;
use App\Models\BlogImage;
use App\Models\BlogCategory;

use App\Http\Requests\RssFeed\StoreRssFeedRequest;
use App\Http\Requests\RssFeed\UpdateRssFeedRequest;
use Auth;

class RssFeedItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 
              
        try{
            $data['result'] = RssFeed::getFeedLists($request);
            // echo json_encode($data['result']);exit;
            $sourceArr = array();
            if(isset($_GET['category_id']) && $_GET['category_id']!=''){
                $source = RssFeed::where('status',1)->where('category_id',$_GET['category_id'])->with('category')->orderBy('rss_name')->get();
            }else{                
                $source = RssFeed::where('status',1)->with('category')->orderBy('rss_name')->get();                
            }
            // echo json_encode($source);exit;
            if(count($source)){
                foreach($source as $row){  
                    if (\Helpers::isValidRssUrl($row->rss_url)) {
                        array_push($sourceArr,$row);
                    }
                }
            }
            $data['sources'] = $sourceArr;
            $data['categories'] = Category::where('status',1)->with('sub_category')->get();
            // echo json_encode($data);exit;
            return view('admin/rss-feed-item.index',$data); 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRssFeedRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = $request->all();
        // echo json_encode($post);exit;
        $post['image'] = strtok($post['image'], '?');
        $slug = \Helpers::createSlug($post['title'],'blog',0,false);
        $inject = array(
            'slug'=>$slug,
            'type'=>"post",
            'title'=>$post['title'],
            'description'=>"<p>".$post['description']."</p>",
            'seo_title'=>$post['title'],
            'seo_description'=>$post['description'],
            'source_link'=>$post['url'],
            'source_name'=>$post['source_name'],
            // 'author_name'=>$post['author'],
            'created_by'=>Auth::User()->id,
            'schedule_date' => date("Y-m-d H:i:s",strtotime($post['pubDate'])),
            'status'=>2,
            'created_at'=>date('Y-m-d H:i:s')
        );
        $blog_id = Blog::insertGetId($inject);
        if($blog_id){
            $languages = Language::where('status',1)->get();
            foreach ($languages as $language) 
            {
                $injectTransLation = array(
                    'blog_id' =>$blog_id,
                    'language_code' =>setting('preferred_site_language'),
                    'title' =>$post['title'],
                    'description' =>$post['description'],
                    'created_at' =>date("Y-m-d H:i:s"),
                );
                BlogTranslation::insertGetId($injectTransLation);
            }
            if($post['image']!=''){
                $uploadImage = \Helpers::uploadFilesThroughUrlAfterResizeCompress($post['image'],'blog/');
                // echo json_encode($uploadImage);exit;
                if($uploadImage['status']==true){
                    $blog_image = $uploadImage['file_name'];
                    $image_arr = array(
                        'image'=>$blog_image,
                        'blog_id'=>$blog_id,
                        'created_at'=>date("Y-m-d H:i:s")
                    );
                    BlogImage::insert($image_arr);
                }
            }
            if($post['category_id']!=''){         
                $category = Category::where('id',$post['category_id'])->first();    
                if($category){                    
                    $image_arr = array(
                        'category_id'=>$post['category_id'],
                        'blog_id'=>$blog_id,
                        'created_at'=>date("Y-m-d H:i:s")
                    );
                    if($category->parent_id==0){
                        $image_arr['type'] = 'category';
                    }else{
                        $image_arr['type'] = 'subcategory';
                    }
                    BlogCategory::insert($image_arr);
                }  
                
            }
            return redirect()->back()->with('success', "Data added successfully."); 
        }        
    }

    

    /**
     * Update the translation of specified category in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
    **/
    public function getSource(Request $request)
    {
        $post = $request->all();
        $sourceArr = array();
        $source = RssFeed::where('category_id',$post['category_id'])->get();
        if(count($source)){
            foreach($source as $row){  
                if (\Helpers::isValidRssUrl($row->rss_url)) {
                    array_push($sourceArr,$row);
                }
            }
        }
        $sendArr = array(
            'source'=>$sourceArr,
        );
        $data['source'] = $sourceArr;
        $data['post'] = $post;
        $data['html'] = view('admin.rss-feed-item.partials.source')->with($sendArr)->render();
        $response = [
            'status' => true,
            'message' => "Data fetched successfully.",
            'data' => $data
        ];
        return response($response);
    }
}
