<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use App\Models\Blog;
use App\Models\BlogImage;
use App\Models\BlogTranslation;
use App\Models\Language;


class NewsApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        try {
            $result = \Helpers::getNewsApiLists($request);
            // echo json_encode($result);exit;
            $data['result'] = $result['data'];
            $data['source'] = $result['source'];
            $data['api_language'] = $result['news_api_language'];
            return view('admin/news-api.index',$data);
        } 
        catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $post = $request->all();
        $post['urlToImage'] = strtok($post['urlToImage'], '?');
        $slug = \Helpers::createSlug($post['title'],'blog',0,false);
        $inject = array(
            'slug'=>$slug,
            'type'=>"post",
            'title'=>$post['title'],
            'description'=>"<p>".$post['description']."</p>",
            'seo_title'=>$post['title'],
            'seo_description'=>$post['description'],
            'source_link'=>$post['url'],
            'source_name'=>$post['source'],
            'author_name'=>$post['author'],
            // 'description'=>$post['content'],
            'created_by'=>Auth::User()->id,
            'schedule_date' => date("Y-m-d H:i:s",strtotime($post['publishedAt'])),
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
            if($post['urlToImage']!=''){
                $uploadImage = \Helpers::uploadFilesThroughUrlAfterResizeCompress($post['urlToImage'],'blog/');
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
            return redirect()->back()->with('success', "Data added successfully."); 
        }        
    }
}
