<!-- @if($row->key == 'homepage_theme')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="homepage_theme">{{__('lang.admin_home_page_theme')}}</label>
    <select name="homepage_theme" class="form-control">
        <option value="" >{{__('lang.admin_select_home_page_theme')}}</option>
        <option @if($row->value == 'home_1') selected  @endif value="home_1" >{{__('lang.theme_1')}}</option>
        <option @if($row->value == 'home_2') selected  @endif value="home_2" >{{__('lang.theme_2')}}</option>
        <option @if($row->value == 'home_3') selected  @endif value="home_3" >{{__('lang.theme_3')}}</option>
        <option @if($row->value == 'home_4') selected  @endif value="home_4" >{{__('lang.theme_4')}}</option>
        <option @if($row->value == 'home_5') selected  @endif value="home_5" >{{__('lang.theme_5')}}</option>
    </select>
</div>
@endif
@if($row->key == 'layout')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="layout">{{__('lang.admin_blog_datail_theme')}}</label>
    <select name="layout" class="form-control">
        <option value="">{{__('lang.admin_blog_datail_theme_placeholder')}}</option>
        <option @if($row->value == 'index_1') selected  @endif value="index_1" >{{__('lang.admin_theme_1')}}</option>
        <option @if($row->value == 'index_2') selected  @endif value="index_2" >{{__('lang.admin_theme_2')}}</option>
    </select>
</div>
@endif
@if($row->key == 'site_name')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_name">{{__('lang.admin_website_name')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_website_name_placeholder')}}"  name="site_name"/>
</div>
@endif -->
@if($row->key == 'site_admin_name')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_admin_name">{{__('lang.admin_website_admin_name')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_website_admin_name_placeholder')}}"  name="site_admin_name"/>
</div>
@endif
<!-- @if($row->key == 'site_phone')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_phone">{{__('lang.admin_top_phone_number')}}</label>
    <input type="text" class="form-control " value="{{$row->value}}" placeholder="{{__('lang.admin_top_phone_number_placeholder')}}"  name="site_phone"/>
</div>
@endif -->
@if($row->key == 'from_email')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="from_email">{{__('lang.admin_email_from')}}</label>
    <input type="email" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_email_from_placeholder')}}"  name="from_email"/>
</div>
@endif
 <!-- @if($row->key == 'footer_about')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="footer_about">{{__('lang.admin_footer_about_us_info')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_footer_about_us_info_placeholder')}}"  name="footer_about"/>
</div>
@endif
@if($row->key == 'powered_by')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="powered_by">{{__('lang.admin_powered_by')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_powered_by_placeholder')}}"  name="powered_by"/>
</div>
@endif
@if($row->key == 'site_seo_title')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_seo_title">{{__('lang.admin_seo_title')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_seo_title_placeholder')}}"  name="site_seo_title"/>
</div>
@endif
@if($row->key == 'site_seo_description')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_seo_description">{{__('lang.admin_seo_description')}}</label>
    <textarea type="text" class="form-control" value="{{$row->value}}" name="site_seo_description" placeholder="{{__('lang.admin_seo_description_placeholder')}}">{{$row->value}}</textarea>
</div>
@endif
@if($row->key == 'site_seo_keyword')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_seo_keyword">{{__('lang.admin_seo_keyword')}}</label>
    <textarea type="text" class="form-control" value="{{$row->value}}" name="site_seo_keyword" placeholder="{{__('lang.admin_seo_keyword_placeholder')}}">{{$row->value}}</textarea>
</div>
@endif
@if($row->key == 'site_seo_tag')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="site_seo_tag">{{__('lang.admin_seo_tags')}}</label>
    <textarea type="text" class="form-control" value="{{$row->value}}" name="site_seo_tag" placeholder="{{__('lang.admin_seo_tags_placeholder')}}">{{$row->value}}</textarea>
</div>
@endif -->
@if($row->key == 'preferred_site_language')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="preferred_site_language">{{__('lang.admin_preferred_site_language')}}</label>
    <select name="preferred_site_language" class="form-control">
        <option value="">{{__('lang.admin_preferred_site_language_placeholder')}}</option>
        @foreach($languages as $language)
            <option @if($row->value == $language->code) selected  @endif value="{{$language->code}}">{{$language->name}}</option>
        @endforeach
    </select>
</div>
@endif
@if($row->key == 'news_api_key')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="news_api_key">{{__('lang.admin_news_api_key')}}</label>
    <input type="text" class="form-control" name="news_api_key" value="{{$row->value}}" placeholder="{{__('lang.admin_news_api_key_placeholder')}}">
</div>
@endif
<!-- @if($row->key == 'chat_gpt_api_key')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="chat_gpt_api_key">{{__('lang.chat_gpt_api_key')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.chat_gpt_api_key_placeholder')}}" value="{{$row->value}}" name="chat_gpt_api_key"/>
</div>
@endif
@if($row->key == 'google_translation_api_key')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="google_translation_api_key">{{__('lang.google_translation_api_key')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.google_translation_api_key_placeholder')}}" value="{{$row->value}}" name="google_translation_api_key"/>
</div>
@endif -->
<!-- @if($row->key == 'google_analytics_code')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="google_analytics_code">{{__('lang.admin_google_analytics_code')}}</label>
    <textarea type="text" class="form-control" name="google_analytics_code" value="{{$row->value}}" placeholder="{{__('lang.admin_google_analytics_code_placeholder')}}">{{$row->value}}</textarea>
</div>
@endif  -->
<!-- @if($row->key == 'site_logo')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_website_logo')}}</label>
    <div class="d-flex">
    <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="site-logo-preview" alt="site_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
    <div class="mt-75 ms-1">
        <label class="btn btn-primary me-75 mb-0" for="change-site-logo">
        <span class="d-none d-sm-block">{{__('lang.admin_upload_website_logo')}}</span>
        <input class="form-control" type="file" name="site_logo" id="change-site-logo" hidden accept="image/*" name="site_logo" onclick="showImagePreview('change-site-logo','site-logo-preview',408,115);"/>
        <span class="d-block d-sm-none">
            <i class="me-0" data-feather="edit"></i>
        </span>
        </label>
        <p>{{__('lang.admin_upload_website_logo_resolution')}}</p>
    </div>
    </div>
</div>
@endif -->
@if($row->key == 'website_admin_logo')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_website_admin_logo')}}</label>
    <div class="d-flex">
    <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="website-admin-logo-preview" alt="website_admin_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
    <div class="mt-75 ms-1">
        <label class="btn btn-primary me-75 mb-0" for="change-website-admin-logo">
        <span class="d-none d-sm-block">{{__('lang.admin_upload_website_admin_logo')}}</span>
        <input class="form-control" type="file" name="website_admin_logo" id="change-website-admin-logo" hidden accept="image/*" name="website_admin_logo" onclick="showImagePreview('change-website-admin-logo','website-admin-logo-preview',512,512);"/>
        <span class="d-block d-sm-none">
            <i class="me-0" data-feather="edit"></i>
        </span>
        </label>
        <p>{{__('lang.admin_website_admin_logo_resolution')}}</p>
    </div>
    </div>
</div>
@endif
@if($row->key == 'site_favicon')
<div class="col-md-5 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_website_favicon')}}</label>
    <div class="d-flex">
        <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="website-favicon-preview" alt="site_favicon" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
        <div class="mt-75 ms-1">
            <label class="btn btn-primary me-75 mb-0" for="change-website-favicon">
            <span class="d-none d-sm-block">{{__('lang.admin_upload_website_favicon')}}</span>
            <input class="form-control" type="file" name="site_favicon" id="change-website-favicon" hidden accept="image/*" name="site_favicon" onclick="showImagePreview('change-website-favicon','website-favicon-preview',512,512);"/>
            <span class="d-block d-sm-none">
                <i class="me-0" data-feather="edit"></i>
            </span>
            </label>
            <p>{{__('lang.admin_website_favicon_resolution')}}</p>
        </div>
    </div>
</div>
@endif