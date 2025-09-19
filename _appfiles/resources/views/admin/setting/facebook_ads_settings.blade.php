@if($row->key == 'enable_fb_ads')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="enable_fb_ads" id="enable_fb_ads" @if($row->value == 1) checked="" @endif>
        <label class="form-check-label" for="enable_fb_ads">{{__('lang.admin_enable_fb_ads_placeholder')}}</label>
    </div>
</div>
@endif
@if($row->key == 'fb_ads_placement_id_android')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_placement_id_android">{{__('lang.admin_fb_ads_placement_id_android')}}</label>
    <input type="text" class="form-control" name="fb_ads_placement_id_android" placeholder="{{__('lang.admin_fb_ads_placement_id_android_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'fb_ads_placement_id_ios')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_placement_id_ios">{{__('lang.admin_fb_ads_placement_id_ios')}}</label>
    <input type="text" class="form-control" name="fb_ads_placement_id_ios" placeholder="{{__('lang.admin_fb_ads_placement_id_ios_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'fb_ads_app_token')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_app_token">{{__('lang.admin_fb_ads_app_token')}}</label>
    <input type="text" class="form-control" name="fb_ads_app_token" placeholder="{{__('lang.admin_fb_ads_app_token_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'fb_ads_frequency')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_frequency">{{__('lang.admin_fb_ads_frequency')}}</label>
    <input type="text" class="form-control" name="fb_ads_frequency" placeholder="{{__('lang.admin_fb_ads_frequency_placeholder')}}" value="{{$row->value}}" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
</div>
@endif
@if($row->key == 'fb_ads_interstitial_id_android')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_interstitial_id_android">{{__('lang.admin_fb_ads_interstitial_id_android')}}</label>
    <input type="text" class="form-control" name="fb_ads_interstitial_id_android" placeholder="{{__('lang.admin_fb_ads_interstitial_id_android_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'fb_ads_interstitial_id_ios')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="fb_ads_interstitial_id_ios">{{__('lang.admin_fb_ads_interstitial_id_ios')}}</label>
    <input type="text" class="form-control" name="fb_ads_interstitial_id_ios" placeholder="{{__('lang.admin_fb_ads_interstitial_id_ios_placeholder')}}" value="{{$row->value}}">
</div>
@endif