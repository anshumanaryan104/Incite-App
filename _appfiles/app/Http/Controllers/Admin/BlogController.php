<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Visibility;
use App\Models\Language;
use App\Models\BlogImage;
use App\Models\DeviceToken;
use App\Models\BlogBookmark;
use App\Models\LanguageCode;
use App\Models\BlogAnalytic;

use App\Http\Requests\Blog\StoreBlogRequest;
use App\Http\Requests\Blog\UpdateBlogRequest;
use App\Http\Requests\Blog\UpdateBlogTranslationRequest;

use App\Http\Requests\Blog\StoreQuoteRequest;
use App\Http\Requests\Blog\UpdateQuoteRequest;
use App\Http\Requests\Blog\UpdateQuoteTranslationRequest;
use Illuminate\Support\Facades\Session;
use Kyslik\ColumnSortable\Sortable;
use DB;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function index(Request $request)
    {
        // $language_codes = array(
        //     array('id' => '1','code' => 'ab','name' => 'Abkhaz'),
        //     array('id' => '2','code' => 'aa','name' => 'Afar'),
        //     array('id' => '3','code' => 'af','name' => 'Afrikaans'),
        //     array('id' => '4','code' => 'ak','name' => 'Akan'),
        //     array('id' => '5','code' => 'sq','name' => 'Albanian'),
        //     array('id' => '6','code' => 'am','name' => 'Amharic'),
        //     array('id' => '7','code' => 'ar','name' => 'Arabic'),
        //     array('id' => '8','code' => 'an','name' => 'Aragonese'),
        //     array('id' => '9','code' => 'hy','name' => 'Armenian'),
        //     array('id' => '10','code' => 'as','name' => 'Assamese'),
        //     array('id' => '11','code' => 'av','name' => 'Avaric'),
        //     array('id' => '12','code' => 'ae','name' => 'Avestan'),
        //     array('id' => '13','code' => 'ay','name' => 'Aymara'),
        //     array('id' => '14','code' => 'az','name' => 'Azerbaijani'),
        //     array('id' => '15','code' => 'bm','name' => 'Bambara'),
        //     array('id' => '16','code' => 'ba','name' => 'Bashkir'),
        //     array('id' => '17','code' => 'eu','name' => 'Basque'),
        //     array('id' => '18','code' => 'be','name' => 'Belarusian'),
        //     array('id' => '19','code' => 'bn','name' => 'Bengali'),
        //     array('id' => '20','code' => 'bh','name' => 'Bihari'),
        //     array('id' => '21','code' => 'bi','name' => 'Bislama'),
        //     array('id' => '22','code' => 'bs','name' => 'Bosnian'),
        //     array('id' => '23','code' => 'br','name' => 'Breton'),
        //     array('id' => '24','code' => 'bg','name' => 'Bulgarian'),
        //     array('id' => '25','code' => 'my','name' => 'Burmese'),
        //     array('id' => '26','code' => 'ca','name' => 'Catalan'),
        //     array('id' => '27','code' => 'ch','name' => 'Chamorro'),
        //     array('id' => '28','code' => 'ce','name' => 'Chechen'),
        //     array('id' => '29','code' => 'ny','name' => 'Chichewa'),
        //     array('id' => '30','code' => 'zh','name' => 'Chinese'),
        //     array('id' => '31','code' => 'cv','name' => 'Chuvash'),
        //     array('id' => '32','code' => 'kw','name' => 'Cornish'),
        //     array('id' => '33','code' => 'co','name' => 'Corsican'),
        //     array('id' => '34','code' => 'cr','name' => 'Cree'),
        //     array('id' => '35','code' => 'hr','name' => 'Croatian'),
        //     array('id' => '36','code' => 'cs','name' => 'Czech'),
        //     array('id' => '37','code' => 'da','name' => 'Danish'),
        //     array('id' => '38','code' => 'dv','name' => 'Divehi'),
        //     array('id' => '39','code' => 'nl','name' => 'Dutch'),
        //     array('id' => '40','code' => 'en','name' => 'English'),
        //     array('id' => '41','code' => 'eo','name' => 'Esperanto'),
        //     array('id' => '42','code' => 'et','name' => 'Estonian'),
        //     array('id' => '43','code' => 'ee','name' => 'Ewe'),
        //     array('id' => '44','code' => 'fo','name' => 'Faroese'),
        //     array('id' => '45','code' => 'fj','name' => 'Fijian'),
        //     array('id' => '46','code' => 'fi','name' => 'Finnish'),
        //     array('id' => '47','code' => 'fr','name' => 'French'),
        //     array('id' => '48','code' => 'ff','name' => 'Fula'),
        //     array('id' => '49','code' => 'gl','name' => 'Galician'),
        //     array('id' => '50','code' => 'ka','name' => 'Georgian'),
        //     array('id' => '51','code' => 'de','name' => 'German'),
        //     array('id' => '52','code' => 'el','name' => 'Greek'),
        //     array('id' => '53','code' => 'gn','name' => 'Guaraní'),
        //     array('id' => '54','code' => 'gu','name' => 'Gujarati'),
        //     array('id' => '55','code' => 'ht','name' => 'Haitian'),
        //     array('id' => '56','code' => 'ha','name' => 'Hausa'),
        //     array('id' => '57','code' => 'he','name' => 'Hebrew (modern)'),
        //     array('id' => '58','code' => 'hz','name' => 'Herero'),
        //     array('id' => '59','code' => 'hi','name' => 'Hindi'),
        //     array('id' => '60','code' => 'ho','name' => 'Hiri Motu'),
        //     array('id' => '61','code' => 'hu','name' => 'Hungarian'),
        //     array('id' => '62','code' => 'ia','name' => 'Interlingua'),
        //     array('id' => '63','code' => 'id','name' => 'Indonesian'),
        //     array('id' => '64','code' => 'ie','name' => 'Interlingue'),
        //     array('id' => '65','code' => 'ga','name' => 'Irish'),
        //     array('id' => '66','code' => 'ig','name' => 'Igbo'),
        //     array('id' => '67','code' => 'ik','name' => 'Inupiaq'),
        //     array('id' => '68','code' => 'io','name' => 'Ido'),
        //     array('id' => '69','code' => 'is','name' => 'Icelandic'),
        //     array('id' => '70','code' => 'it','name' => 'Italian'),
        //     array('id' => '71','code' => 'iu','name' => 'Inuktitut'),
        //     array('id' => '72','code' => 'ja','name' => 'Japanese'),
        //     array('id' => '73','code' => 'jv','name' => 'Javanese'),
        //     array('id' => '74','code' => 'kl','name' => 'Kalaallisut'),
        //     array('id' => '75','code' => 'kn','name' => 'Kannada'),
        //     array('id' => '76','code' => 'kr','name' => 'Kanuri'),
        //     array('id' => '77','code' => 'ks','name' => 'Kashmiri'),
        //     array('id' => '78','code' => 'kk','name' => 'Kazakh'),
        //     array('id' => '79','code' => 'km','name' => 'Khmer'),
        //     array('id' => '80','code' => 'ki','name' => 'Kikuyu'),
        //     array('id' => '81','code' => 'rw','name' => 'Kinyarwanda'),
        //     array('id' => '82','code' => 'ky','name' => 'Kirghiz'),
        //     array('id' => '83','code' => 'kv','name' => 'Komi'),
        //     array('id' => '84','code' => 'kg','name' => 'Kongo'),
        //     array('id' => '85','code' => 'ko','name' => 'Korean'),
        //     array('id' => '86','code' => 'ku','name' => 'Kurdish'),
        //     array('id' => '87','code' => 'kj','name' => 'Kwanyama'),
        //     array('id' => '88','code' => 'la','name' => 'Latin'),
        //     array('id' => '89','code' => 'lb','name' => 'Luxembourgish'),
        //     array('id' => '90','code' => 'lg','name' => 'Luganda'),
        //     array('id' => '91','code' => 'li','name' => 'Limburgish'),
        //     array('id' => '92','code' => 'ln','name' => 'Lingala'),
        //     array('id' => '93','code' => 'lo','name' => 'Lao'),
        //     array('id' => '94','code' => 'lt','name' => 'Lithuanian'),
        //     array('id' => '95','code' => 'lu','name' => 'Luba-Katanga'),
        //     array('id' => '96','code' => 'lv','name' => 'Latvian'),
        //     array('id' => '97','code' => 'gv','name' => 'Manx'),
        //     array('id' => '98','code' => 'mk','name' => 'Macedonian'),
        //     array('id' => '99','code' => 'mg','name' => 'Malagasy'),
        //     array('id' => '100','code' => 'ms','name' => 'Malay'),
        //     array('id' => '101','code' => 'ml','name' => 'Malayalam'),
        //     array('id' => '102','code' => 'mt','name' => 'Maltese'),
        //     array('id' => '103','code' => 'mi','name' => 'Māori'),
        //     array('id' => '104','code' => 'mr','name' => 'Marathi (Marāṭhī)'),
        //     array('id' => '105','code' => 'mh','name' => 'Marshallese'),
        //     array('id' => '106','code' => 'mn','name' => 'Mongolian'),
        //     array('id' => '107','code' => 'na','name' => 'Nauru'),
        //     array('id' => '108','code' => 'nv','name' => 'Navajo'),
        //     array('id' => '109','code' => 'nb','name' => 'Norwegian Bokmål'),
        //     array('id' => '110','code' => 'nd','name' => 'North Ndebele'),
        //     array('id' => '111','code' => 'ne','name' => 'Nepali'),
        //     array('id' => '112','code' => 'ng','name' => 'Ndonga'),
        //     array('id' => '113','code' => 'nn','name' => 'Norwegian Nynorsk'),
        //     array('id' => '114','code' => 'no','name' => 'Norwegian'),
        //     array('id' => '115','code' => 'ii','name' => 'Nuosu'),
        //     array('id' => '116','code' => 'nr','name' => 'South Ndebele'),
        //     array('id' => '117','code' => 'oc','name' => 'Occitan'),
        //     array('id' => '118','code' => 'oj','name' => 'Ojibwe'),
        //     array('id' => '119','code' => 'cu','name' => 'Old Church Slavonic'),
        //     array('id' => '120','code' => 'om','name' => 'Oromo'),
        //     array('id' => '121','code' => 'or','name' => 'Oriya'),
        //     array('id' => '122','code' => 'os','name' => 'Ossetian'),
        //     array('id' => '123','code' => 'pa','name' => 'Panjabi'),
        //     array('id' => '124','code' => 'pi','name' => 'Pāli'),
        //     array('id' => '125','code' => 'fa','name' => 'Persian'),
        //     array('id' => '126','code' => 'pl','name' => 'Polish'),
        //     array('id' => '127','code' => 'ps','name' => 'Pashto'),
        //     array('id' => '128','code' => 'pt','name' => 'Portuguese'),
        //     array('id' => '129','code' => 'qu','name' => 'Quechua'),
        //     array('id' => '130','code' => 'rm','name' => 'Romansh'),
        //     array('id' => '131','code' => 'rn','name' => 'Kirundi'),
        //     array('id' => '132','code' => 'ro','name' => 'Romanian'),
        //     array('id' => '133','code' => 'ru','name' => 'Russian'),
        //     array('id' => '134','code' => 'sa','name' => 'Sanskrit (Saṁskṛta)'),
        //     array('id' => '135','code' => 'sc','name' => 'Sardinian'),
        //     array('id' => '136','code' => 'sd','name' => 'Sindhi'),
        //     array('id' => '137','code' => 'se','name' => 'Northern Sami'),
        //     array('id' => '138','code' => 'sm','name' => 'Samoan'),
        //     array('id' => '139','code' => 'sg','name' => 'Sango'),
        //     array('id' => '140','code' => 'sr','name' => 'Serbian'),
        //     array('id' => '141','code' => 'gd','name' => 'Scottish Gaelic; Gaelic'),
        //     array('id' => '142','code' => 'sn','name' => 'Shona'),
        //     array('id' => '143','code' => 'si','name' => 'Sinhala'),
        //     array('id' => '144','code' => 'sk','name' => 'Slovak'),
        //     array('id' => '145','code' => 'sl','name' => 'Slovene'),
        //     array('id' => '146','code' => 'so','name' => 'Somali'),
        //     array('id' => '147','code' => 'st','name' => 'Southern Sotho'),
        //     array('id' => '148','code' => 'es','name' => 'Spanish'),
        //     array('id' => '149','code' => 'su','name' => 'Sundanese'),
        //     array('id' => '150','code' => 'sw','name' => 'Swahili'),
        //     array('id' => '151','code' => 'ss','name' => 'Swati'),
        //     array('id' => '152','code' => 'sv','name' => 'Swedish'),
        //     array('id' => '153','code' => 'ta','name' => 'Tamil'),
        //     array('id' => '154','code' => 'te','name' => 'Telugu'),
        //     array('id' => '155','code' => 'tg','name' => 'Tajik'),
        //     array('id' => '156','code' => 'th','name' => 'Thai'),
        //     array('id' => '157','code' => 'ti','name' => 'Tigrinya'),
        //     array('id' => '158','code' => 'bo','name' => 'Tibetan Standard'),
        //     array('id' => '159','code' => 'tk','name' => 'Turkmen'),
        //     array('id' => '160','code' => 'tl','name' => 'Tagalog'),
        //     array('id' => '161','code' => 'tn','name' => 'Tswana'),
        //     array('id' => '162','code' => 'to','name' => 'Tonga (Tonga Islands)'),
        //     array('id' => '163','code' => 'tr','name' => 'Turkish'),
        //     array('id' => '164','code' => 'ts','name' => 'Tsonga'),
        //     array('id' => '165','code' => 'tt','name' => 'Tatar'),
        //     array('id' => '166','code' => 'tw','name' => 'Twi'),
        //     array('id' => '167','code' => 'ty','name' => 'Tahitian'),
        //     array('id' => '168','code' => 'ug','name' => 'Uighur'),
        //     array('id' => '169','code' => 'uk','name' => 'Ukrainian'),
        //     array('id' => '170','code' => 'ur','name' => 'Urdu'),
        //     array('id' => '171','code' => 'uz','name' => 'Uzbek'),
        //     array('id' => '172','code' => 've','name' => 'Venda'),
        //     array('id' => '173','code' => 'vi','name' => 'Vietnamese'),
        //     array('id' => '174','code' => 'vo','name' => 'Volapük'),
        //     array('id' => '175','code' => 'wa','name' => 'Walloon'),
        //     array('id' => '176','code' => 'cy','name' => 'Welsh'),
        //     array('id' => '177','code' => 'wo','name' => 'Wolof'),
        //     array('id' => '178','code' => 'fy','name' => 'Western Frisian'),
        //     array('id' => '179','code' => 'xh','name' => 'Xhosa'),
        //     array('id' => '180','code' => 'yi','name' => 'Yiddish'),
        //     array('id' => '181','code' => 'yo','name' => 'Yoruba'),
        //     array('id' => '182','code' => 'za','name' => 'Zhuang')
        // );
        // // $language = array(
        // //     array('name' => 'English','code' => 'en','position' => 'ltr','status' => 1),
        // // );
        // foreach ($language_codes as $key => $value) {
        //     LanguageCode::insert([
        //          'id' => $value['id'],
        //          'name' => $value['name'],
        //          'code' => $value['code'],
        //     ]);
        // } 
        Session::regenerate();   
        $data['result'] = Blog::getLists($request->all());
            $data['category'] = Category::where('status',1)->where('parent_id',0)->get();
            $data['visibility'] = Visibility::where('status',1)->get();
            return view('admin.blog.index',$data);
        // try{
        //     $data['result'] = Blog::getLists($request->all());
        //     $data['category'] = Category::where('status',1)->where('parent_id',0)->get();
        //     $data['visibility'] = Visibility::where('status',1)->get();
        //     return view('admin.blog.index',$data);
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    /**
     * Show the form for creating a new blog.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type)
    {
        try {
                     
            $data['images'] = BlogImage::where('session_id',Session::get('session_id'))->orderBy('order','ASC')->get();
            $data['categories'] = Category::where('parent_id',0)->orderBy('name','ASC')->get();
            $data['visibility'] = Visibility::latest('created_at')->get();
            return view('admin.blog.create_'.$type.'',$data);
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBlogRequest $request)
    {       
        $validated = $request->validated();
            $added = Blog::addUpdate($request->all());
            if($added['status']==true){
                return redirect('admin/blog')->with('success', $added['message']); 
            }
            else{
                return redirect()->back()->with('error', $added['message']);
            } 
        // try{
        //     $validated = $request->validated();
        //     $added = Blog::addUpdate($request->all());
        //     if($added['status']==true){
        //         return redirect('admin/blog')->with('success', $added['message']); 
        //     }
        //     else{
        //         return redirect()->back()->with('error', $added['message']);
        //     }
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function storeQuote(StoreQuoteRequest $request)
    {        
        try{
            $validated = $request->validated();
            
            $added = Blog::addUpdateQuote($request->all());
            if($added['status']==true){
                return redirect('admin/blog')->with('success', $added['message']); 
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
     * Show the form for editing the specified resource.
     *
     * @param  type $type, id  $id 
     * @return \Illuminate\Http\Response
     */
    public function edit($type,$id)
    {        
        try {
            $data['images'] = BlogImage::where('blog_id',$id)->orderBy('order','ASC')->get();
            
            $data['categories'] = Category::where('parent_id',0)->orderBy('name','ASC')->get();
            $data['subcategory'] = array();
            $data['visibility'] = Visibility::latest('created_at')->get();
            $data['voice_accent'] = config('constant.voice_accent');
            $data['speech_voice'] = config('constant.speech_voice');
            $data['row'] = Blog::getDetail($id);
            if($data['row']!=''){
                if(isset($data['row']->categoryArr) && count($data['row']->categoryArr)){
                    $data['subcategory'] = Category::whereIn('parent_id',$data['row']->categoryArr)->get();
                }
            }
            // echo json_encode($data);exit;
            return view('admin.blog.edit_'.$type.'',$data);
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBlogRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBlogRequest $request)
    {
        try{
            $validated = $request->validated();

            $updated = Blog::addUpdate($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/blog')->with('success', $updated['message']); 
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
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateQuoteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function updateQuote(UpdateQuoteRequest $request)
    {        
        try{
            $validated = $request->validated();
            $updated = Blog::addUpdateQuote($request->all(),$request->input('id'));
            if($updated['status']==true){
                return redirect('admin/blog')->with('success', $updated['message']); 
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
     * Remove the specified resource from Blog.
     * @param  Request $request
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {
        try{
            $deleted = Blog::deleteRecord($id);
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
     * Remove the specified category from storage.
     *
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function changeStatus($id,$status)
    {
        try{
            $updated = Blog::changeStatus($status,$id);
            if($updated['status']){
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
     * Get translations of specified category from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function translation($id)
    {
        try{
            $data['detail'] = Blog::getDetail($id);
            $data['languages'] = Blog::getTranslation($id);
            return view('admin/blog.translation',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  \App\Http\Requests\UpdateBlogTranslationRequest  $request
     * @param  id  $id
     * @return \Illuminate\Http\Response
    **/
    public function updateTranslation(UpdateBlogTranslationRequest $request,$id)
    {
        $validated = $request->validated();
        $translationUpdated = Blog::updateTranslation($request->all(),$id);
        if($translationUpdated['status']==true){
            return redirect('admin/blog')->with('success', $translationUpdated['message']); 
        }
        else{
            return redirect()->back()->with('error', $translationUpdated['message']);
        } 
    }
    /**
     * Update the translation of specified category in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
    **/
    public function getSubcategories(Request $request)
    {
        $post = $request->all();
        $subcategory = Category::whereIn('parent_id',$post['category_id'])->get();
        $sendArr = array(
            'subcategory'=>$subcategory,
        );
        if(isset($post['sub_category_id']) && count($post['sub_category_id'])){
            $sendArr['sub_cat_id'] = $post['sub_category_id']; 
        }

        $data['html'] = view('admin.blog.partials.subcategory')->with($sendArr)->render();
        $response = [
            'status' => true,
            'message' => "Data fetched successfully.",
            'data' => $data
        ];
        return response($response);
    }

    public function storeImage(Request $request)
    {    
        $files = $request->file('file'); 
            $uploadImage = \Helpers::uploadFilesAfterResizeCompressOriginalName($files,'blog');
            
            if($uploadImage['status']==true){
                $postArr = array(
                    'session_id'=>Session::get('session_id'),
                    'image'=>$uploadImage['file_name'],
                    'created_at'=>date('Y-m-d H:i:s')
                );
                if(isset($request->blog_id) && $request->blog_id!=0){
                    $postArr['blog_id'] = $request->blog_id;
                }else{
                    $postArr['session_id'] = Session::get('session_id');
                }
                $image = BlogImage::insertGetId($postArr);
            } 
            if(isset($request->blog_id) && $request->blog_id!=0){ 
                $blog_images = BlogImage::where('blog_id',$request->blog_id)->orderBy('order','ASC')->get();
            }else{
                $blog_images = BlogImage::where('session_id',Session::get('session_id'))->orderBy('order','ASC')->get();
            }
            $data['uploadImage'] = $uploadImage;
            $data['session_id'] = Session::get('session_id');
            $data['blog_images'] = $blog_images;
            $data['html'] = view('admin.blog.partials.image_preview')->with(array('images'=>$blog_images))->render();
            $response = $this->sendResponse($data,__('lang.message_data_retrived_successfully'));
            return $response;
        // try{
            
        //     // $blog_images = BlogImage::where('session_id',Session::get('session_id'))->get();
        //     // $data['html'] = view('admin.blog.partials.image_preview')->with(array('images'=>$blog_images))->render();
        //     // // echo json_encode($data);exit;
        //     // $response = $this->sendResponse($blog_images,__('lang.message_data_retrived_successfully'));
            
        //     // return $response;
        //     // $validated = $request->validated();
            
        //     // $added = Blog::addUpdateQuote($request->all());
        //     // if($added['status']==true){
        //     //     return redirect('admin/blog')->with('success', $added['message']); 
        //     // }
        //     // else{
        //     //     return redirect()->back()->with('error', $added['message']);
        //     // }
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    public function removeImage(Request $request)
    {     
        $files = $request->all();
            // print_r($files);
            $item_id = 0;
            $image = BlogImage::where('id',$request->image_id)->first();
            if($image!=''){
                BlogImage::where('id',$request->image_id)->delete();
                $blog_images = BlogImage::where('session_id',Session::get('session_id'))->orderBy('order','ASC')->get();
                if($image->blog_id!=0){
                    $blog_images = BlogImage::where('blog_id',$image->blog_id)->orderBy('order','ASC')->get();
                }   
                // echo json_encode($blog_images);exit;  
                $data['blog_images'] = $blog_images;
                $data['html'] = view('admin.blog.partials.image_preview')->with(array('images'=>$blog_images))->render();
                $response = $this->sendResponse($data,__('lang.message_data_retrived_successfully'));
                return $response;            
            }
            return $this->sendError(__('lang.message_something_went_wrong'));  
        // try{
            
        //     // $validated = $request->validated();
            
        //     // $added = Blog::addUpdateQuote($request->all());
        //     // if($added['status']==true){
        //     //     return redirect('admin/blog')->with('success', $added['message']); 
        //     // }
        //     // else{
        //     //     return redirect()->back()->with('error', $added['message']);
        //     // }
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    public function removeImageByName(Request $request)
    {     
        $files = $request->all();
            // print_r($files);
            $item_id = 0;
            if(isset($request->blog_id) && $request->blog_id!=0){
                $image = BlogImage::where('image',$request->filename)->where('blog_id',$request->blog_id)->orderBy('id','DESC')->first();
            }else{
                $image = BlogImage::where('image',$request->filename)->where('session_id',Session::get('session_id'))->orderBy('id','DESC')->first();
            }     
            // echo json_encode($image);exit;          
            if($image!=''){
                BlogImage::where('id',$image->id)->delete();
                $blog_images = BlogImage::where('session_id',Session::get('session_id'))->orderBy('order','ASC')->get();
                if($image->blog_id!=0){
                    $blog_images = BlogImage::where('blog_id',$image->blog_id)->orderBy('order','ASC')->get();
                }   
                // echo json_encode($blog_images);exit;  
                $data['blog_images'] = $blog_images;
                $data['html'] = view('admin.blog.partials.image_preview')->with(array('images'=>$blog_images))->render();
                $response = $this->sendResponse($data,__('lang.message_data_retrived_successfully'));
                return $response;            
            }
            return $this->sendError(__('lang.message_something_went_wrong'));  
        // try{
            
        //     // $validated = $request->validated();
            
        //     // $added = Blog::addUpdateQuote($request->all());
        //     // if($added['status']==true){
        //     //     return redirect('admin/blog')->with('success', $added['message']); 
        //     // }
        //     // else{
        //     //     return redirect()->back()->with('error', $added['message']);
        //     // }
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    /**
     * Update order storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sorting(Request $request)
    {
        try{
            $input = $request->all();
            if(isset($input['blog_id']) && $input['blog_id']!=0){
                $posts = BlogImage::where('blog_id',$input['blog_id'])->get();
            }else{
                $posts = BlogImage::where('session_id',Session::get('session_id'))->get();
            }          
            foreach ($posts as $post) {
                foreach ($request->order as $order) {
                    if ($order['id'] == $post->id) {
                        $c = BlogImage::where('id',$post->id)->update(['order' => $order['position']]);                        
                    }
                }
            }
            $response = [
                'status' => true,
                'message' => __('lang.message_data_retrived_successfully'),
                'data' => []
            ];
            return response($response);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }

    /**
     * Send Notification from storage.
     *
     * @param  id $id
     * @return \Illuminate\Http\Response
    **/
    public function sendNotification($id)
    { 
        if(setting('one_signal_key')==''){
            return redirect()->back()->with('error',__('lang.message_one_signal_key_not_found'));
        }else{
            $blog = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where('id',$id)->where('status',1)->with('blog_category')->with('blog_sub_category')->first();
            $image = url('uploads/setting/'.setting('app_logo'));
            if($blog){
                $blog->images = \Helpers::getBlogImages($blog->id,'327x250');
                if($blog->type=='post'){
                    if(count($blog->images)){
                        $image = $blog->images[0];
                    }
                }else{
                    $image = url('uploads/blog/'.$blog->background_image);
                }                
                if($blog->background_image!=''){
                    $blog->background_image = url('uploads/blog/'.$blog->background_image);
                }
            }
            $player_id = array();
            $token = DeviceToken::select('player_id', DB::raw('MAX(id) as max_id,is_notification_enabled'))->groupBy('player_id')->orderBy('max_id', 'DESC')->get();
            if(count($token)){
                foreach($token as $detail){
                    if($detail->player_id!='' || $detail->player_id!=null || $detail->player_id!="null"){
                        if($detail->is_notification_enabled==1){
                            if(!in_array($detail->player_id,$player_id)){
                                array_push($player_id,$detail->player_id);
                            }
                        }
                    }                   
                }
            }
            $status = \Helpers::sendNotification($blog->title,$blog->description,$image,$blog,$player_id);
            if ($status === 200) {
                return redirect()->back()->with('success', __('lang.message_notification_sent_successfully')); 
            } else {
                return redirect()->back()->with('error', __('lang.message_error_while_sending'));
            }   
        }    
            
        // try{
        //     $blog = Blog::select('id', 'type', 'title','description', 'source_name', 'source_link','voice', 'accent_code','video_url', 'is_voting_enable', 'schedule_date','created_at', 'updated_at', 'background_image')->where('id',$id)->where('status',1)->with('blog_category')->with('blog_sub_category')->first();
        //     $image = url('uploads/setting/'.setting('app_logo'));
        //     if($blog){
        //         $blog->images = \Helpers::getBlogImages($blog->id,'327x250');
        //         if($blog->type=='post'){
        //             if(count($blog->images)){
        //                 $image = $blog->images[0];
        //             }
        //         }else{
        //             $image = url('uploads/blog/'.$blog->background_image);
        //         }                
        //         if($blog->background_image!=''){
        //             $blog->background_image = url('uploads/blog/'.$blog->background_image);
        //         }
        //     }
        //     $player_id = array();
        //     $token = DeviceToken::select('player_id', DB::raw('MAX(id) as max_id,is_notification_enabled'))->groupBy('player_id')->orderBy('max_id', 'DESC')->get();
        //     if(count($token)){
        //         foreach($token as $detail){
        //             if($detail->player_id!='' || $detail->player_id!=null || $detail->player_id!="null"){
        //                 if($detail->is_notification_enabled==1){
        //                     if(!in_array($detail->player_id,$player_id)){
        //                         array_push($player_id,$detail->player_id);
        //                     }
        //                 }
        //             }                   
        //         }
        //     }
        //     $status = \Helpers::sendNotification($blog->title,$blog->description,$image,$blog,$player_id);
        //     if ($status === 200) {
        //         return redirect()->back()->with('success', __('lang.message_notification_sent_successfully')); 
        //     } else {
        //         return redirect()->back()->with('error', __('lang.message_error_while_sending'));
        //     }
        // }
        // catch(\Exception $ex){
        //     return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        // }
    }

    /**
     * Display a listing of the blog.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
    */
    public function analytics(Request $request,$id)
    {
        try{
            $pagination = (isset($search['perpage']))?$search['perpage']:config('constant.pagination');
            $data['views'] = BlogAnalytic::where('type','view')->where('blog_id',$id)->with('user')->paginate($pagination)->appends('perpage', $pagination);
            $data['bookmarks'] = BlogBookmark::where('blog_id',$id)->with('user')->paginate($pagination)->appends('perpage', $pagination);
            $data['shares'] = BlogAnalytic::where('type','share')->where('blog_id',$id)->with('user')->paginate($pagination)->appends('perpage', $pagination);
            return view('admin.blog.analytics',$data);
        }
        catch(\Exception $ex){
            return redirect()->back()->with('error', $ex->getMessage() . ' '. $ex->getLine() . ' '. $ex->getFile()); 
        }
    }
}
