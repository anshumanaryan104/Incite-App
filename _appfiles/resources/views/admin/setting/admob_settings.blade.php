@if($row->key == 'enable_ads')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="enable_ads" id="enable_ads" @if($row->value == 1) checked="" @endif>
        <label class="form-check-label" for="enable_ads">{{__('lang.admin_enable_ads_placeholder')}}</label>
    </div>
</div>
@endif
@if($row->key == 'admob_banner_id_android')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="admob_banner_id_android">{{__('lang.admin_admob_banner_id_android')}} </label>
    <input type="text" class="form-control" name="admob_banner_id_android" placeholder="{{__('lang.admin_admob_banner_id_android_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'admob_interstitial_id_android')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="admob_interstitial_id_android">{{__('lang.admin_admob_interstitial_id_android')}}</label>
    <input type="text" class="form-control" name="admob_interstitial_id_android" placeholder="{{__('lang.admin_admob_interstitial_id_android_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'admob_banner_id_ios')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="admob_banner_id_ios">{{__('lang.admin_admob_banner_id_ios')}}</label>
    <input type="text" class="form-control" name="admob_banner_id_ios" placeholder="{{__('lang.admin_admob_banner_id_ios_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'admob_interstitial_id_ios')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="admob_interstitial_id_ios">{{__('lang.admin_admob_interstitial_id_ios')}}</label>
    <input type="text" class="form-control" name="admob_interstitial_id_ios" placeholder="{{__('lang.admin_admob_interstitial_id_ios_placeholder')}}" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'admob_frequency')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="admob_frequency">{{__('lang.admin_admob_frequency')}}</label>
    <input type="text" class="form-control" name="admob_frequency" placeholder="{{__('lang.admin_admob_frequency_placeholder')}}" value="{{$row->value}}" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))">
</div>
@endif