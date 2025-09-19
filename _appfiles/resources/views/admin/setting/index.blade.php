@extends('admin/layout/app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /</span> {{__('lang.admin_settings')}}</h4>
    <div class="row">
        <div class="col">
            <div class="card mb-3">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link active @if(Request::is('admin/settings/site-setting')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-site-settings"
                        role="tab"
                        aria-selected="true"
                        >
                        {{__('lang.admin_site_settings')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/app-setting')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-app-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_app_settings')}}
                        </button>
                    </li>
                    <!-- <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/app-setting')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-app-theme-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_app_theme_settings')}}
                        </button>
                    </li> -->
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/translation-setting')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-translation-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_translation_settings')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/global-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-global-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_global_settings')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/global-blog-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-global-blog-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_text_speech_settings')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/social-media-login-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-social-media-login-settings"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_social_media_login_settings')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/push-notification-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-push-notification"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_push_notification')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/email-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-email"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_email')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/maintainance-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-maintainance"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_maintainance')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-live-enews"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_live_and_enews')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/admob-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-admob"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_admob')}}
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link @if(Request::is('admin/settings/fb-ads-setting*')) active @endif"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-fb-ad"
                        role="tab"
                        aria-selected="false"
                        >
                        {{__('lang.admin_facebook_ads')}}
                        </button>
                    </li>
                    </ul>
                </div>
                <div class="tab-content">
                    <div class="tab-pane fade active show @if(Request::is('admin/settings/site-setting*')) active show @endif" id="form-tabs-site-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="site-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/site_settings')
                            @endforeach  
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div> 
                        </form>                    
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/app-setting*')) active show @endif" id="form-tabs-app-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="app-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/app_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- <div class="tab-pane fade @if(Request::is('admin/settings/app-theme-setting*')) active show @endif" id="form-tabs-app-theme-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="app-theme-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/app_theme_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div> -->
                    <div class="tab-pane fade @if(Request::is('admin/settings/translation-setting*')) active show @endif" id="form-tabs-translation-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="translation-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/translation_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/global-setting*')) active show @endif" id="form-tabs-global-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="global-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/global_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/social-media-login-setting*')) active show @endif" id="form-tabs-social-media-login-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="social-media-login-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/social_media_login_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/global-blog-setting*')) active show @endif" id="form-tabs-global-blog-settings" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="global-blog-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/global_blog_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/push-notification-setting*')) active show @endif" id="form-tabs-push-notification" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="push-notification-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/push_notification_settings')
                            @endforeach
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/email-setting*')) active show @endif" id="form-tabs-email" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="email-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/email_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/maintainance-setting*')) active show @endif" id="form-tabs-maintainance" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="maintainance-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/maintainance_settings')
                            @endforeach
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div> 
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/news-setting*')) active show @endif" id="form-tabs-live-enews" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="news-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/news_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/admob-setting*')) active show @endif" id="form-tabs-admob" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="admob-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/admob_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade @if(Request::is('admin/settings/fb-ads-setting*')) active show @endif" id="form-tabs-fb-ad" role="tabpanel">
                        <form method="POST" id="update-record" action="{{url('admin/update-setting')}}" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="page_name" value="fb-ads-setting">
                            @csrf
                            @foreach($result as $row)
                                @include('admin/setting/facebook_ads_settings')
                            @endforeach 
                            <div class="row">
                                <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                    <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                    <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection