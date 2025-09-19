<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\RssFeed;
use App\Models\Category;
use App\Models\Language;
use App\Http\Requests\RssFeed\StoreRssFeedRequest;
use App\Http\Requests\RssFeed\UpdateRssFeedRequest;

class RssFeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        try{
            $data['result'] = RssFeed::getLists($request->all());
            $data['languages'] = Language::where('status',1)->get();
            $data['categories'] = Category::where('parent_id',0)->where('status',1)->with('sub_category')->get();
            return view('admin/rss-feed.index',$data);
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
    public function store(StoreRssFeedRequest $request)
    {
        try{
            $validated = $request->validated();
            $added = RssFeed::addUpdate($request->all());
            if($added['status']==true){
                return redirect()->back()->with('success', $added['message']); 
            }
            else{
                return redirect()->back()->with('error', $added['message']);
            }
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRssFeedRequest  $request
     * @param  \App\Models\RssFeed  $RssFeed
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRssFeedRequest $request, RssFeed $RssFeed)
    {
        try{
            $validated = $request->validated();
            $updated = RssFeed::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect()->back()->with('success', $updated['message']); 
            }
            else{
                return redirect()->back()->with('error', $updated['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $deleted = RssFeed::deleteRecord($id);
            if($deleted['status']==true){
                return redirect()->back()->with('success', $deleted['message']); 
            }
            else{
                return redirect()->back()->with('error', $deleted['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * update the specified column from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateColumn($id,$value)
    {
        try{
            $updated = RssFeed::updateColumn($id,$value);
            if($updated['status']==true){
                return redirect()->back()->with('success', $updated['message']); 
            }
            else{
                return redirect()->back()->with('error', $updated['message']);
            } 
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Show the form for editing the specified cms.
     *
     * @param  $id
     * @return \Illuminate\Http\Response
     */
    public function checkFeedItems($id)
    {
        try{
            $rss_feeds = RssFeed::where('id',$id)->where('status',1)->latest('created_at')->first();
            $items = array();
            if($rss_feeds){
                $url = $rss_feeds->rss_url;
                if (\Helpers::isValidRssUrl($url)) {
                    $category_id = $rss_feeds->category_id;
                    $rss = simplexml_load_file($url);
                    if(isset($rss->channel->item) && count($rss->channel->item)){
                        foreach ($rss->channel->item as $item) {
                            $title = (string) $item->title;
                            $link = (string) $item->link;
                            $link = (string) $item->link;
                            if(isset($item->content)){
                                $description = html_entity_decode(strip_tags((string) $item->content));
                            }else{
                                $description = html_entity_decode(strip_tags((string) $item->description));
                            }
                            
                            $pubDate = (string) $item->pubDate;
                            
                            // Attempt to extract the image URL directly from the XML structure
                            $image = null;
                            
                            // Check for 'enclosure' element
                            if (isset($item->enclosure['url'])) {
                                $image = (string) $item->enclosure['url'];
                            }
                            // Check for 'media:content' element
                            elseif (isset($item->children('media', true)->content->attributes()['url'])) {
                                $image = (string) $item->children('media', true)->content->attributes()['url'];
                            }
                            // Check for 'media:thumbnail' element
                            elseif (isset($item->children('media', true)->thumbnail->attributes()['url'])) {
                                $image = (string) $item->children('media', true)->thumbnail->attributes()['url'];
                            }
                            elseif (isset($item->children('media', true)->group->content)) {
                                foreach ($item->children('media', true)->group->content as $content) {
                                    if ($content->attributes()['medium'] == 'image') {
                                        $image = (string) $content->attributes()['url'];
                                        break; // Exit the loop once you find the first image URL
                                    }
                                }
                            }
                            
                            $item_data = [
                                'title' => $title,
                                'link' => $link,
                                'description' => $description,
                                'pubDate' => $pubDate,
                                'image' => $image,
                                'category_id' => $category_id
                            ];
            
                            array_push($items, $item_data);
                        } 
                    }else{
                        foreach ($rss->entry as $entry) {
                            $title = (string) $entry->title;
                            $link = (string) $entry->link;
                            $description = $entry->content;
                            $pubDate = (string) $entry->published;
                            
                            // Attempt to extract the image URL directly from the XML structure
                            $image = null;
                            
                            // Check for 'enclosure' element
                            if (isset($entry->enclosure['url'])) {
                                $image = (string) $entry->enclosure['url'];
                            }
                            // // Check for 'media:content' element
                            elseif (isset($entry->children('media', true)->content->attributes()['url'])) {
                                $image = (string) $entry->children('media', true)->content->attributes()['url'];
                            }
                            // // Check for 'media:thumbnail' element
                            elseif (isset($entry->children('media', true)->thumbnail->attributes()['url'])) {
                                $image = (string) $entry->children('media', true)->thumbnail->attributes()['url'];
                            }
                            
                            $entry_data = [
                                'title' => $title,
                                'link' => $link,
                                'description' => $description,
                                'pubDate' => $pubDate,
                                'image' => $image,
                                'category_id' => $category_id
                            ];
            
                            array_push($items, $entry_data);
                        } 
                    }
                }else{
                    $title = "";
                    $link =  "";
                    $description =  "";
                    $pubDate =  "";
                    $image =  "";
                    $category_id =  "";
                    $entry_data = [
                        'title' => $title,
                        'link' => $link,
                        'description' => $description,
                        'pubDate' => $pubDate,
                        'image' => $image,
                        'category_id' => $category_id
                    ];
    
                    array_push($items, $entry_data);
                }
                
            }
            $data['row'] = $items[0];
            // echo json_encode($data['row']);exit;
            return view('admin/rss-feed.check',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
}
