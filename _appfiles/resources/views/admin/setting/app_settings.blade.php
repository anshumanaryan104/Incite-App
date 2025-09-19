@if($row->key == 'app_name')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="app_name">{{__('lang.admin_app_name')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_app_name_placeholder')}}" name="app_name"/>
</div>
@endif
@if($row->key == 'primary_color')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="primary_color">{{__('lang.admin_primary_color')}}</label>
    <input type="color" id="basic-icon-default-uname" class="form-control dt-uname" name="primary_color" value="{{$row->value}}">
</div>
@endif
@if($row->key == 'secondary_color')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="secondary_color">{{__('lang.admin_secondary_color')}}</label>
    <input type="color" id="basic-icon-default-uname" class="form-control dt-uname" name="secondary_color" value="{{$row->value}}">
</div>
@endif
<!-- @if($row->key == 'bundle_id_android')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="bundle_id_android">{{__('lang.admin_bundle_id_android')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_bundle_id_android_placeholder')}}" name="bundle_id_android"/>
</div>
@endif
@if($row->key == 'bundle_id_ios')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="bundle_id_ios">{{__('lang.admin_bundle_id_ios')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_bundle_id_ios_placeholder')}}" name="bundle_id_ios"/>
</div>
@endif
@if($row->key == 'is_app_force_update')
<div class="col-md-12 mb-3 display-inline-block width-32-percent mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="is_app_force_update" id="is_app_force_update" @if($row->value == 1) checked  @endif>
        <label class="form-check-label" for="is_app_force_update">{{__('lang.admin_is_app_force_update_placeholder')}}</label>
    </div>
</div>
@endif -->
@if($row->key == 'app_logo')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_app_logo')}}</label>
    <div class="d-flex">
    <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="app-logo-preview" alt="app_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
    <div class="mt-75 ms-1">
        <label class="btn btn-primary me-75 mb-0" for="change-app-logo">
        <span class="d-none d-sm-block">{{__('lang.admin_upload_app_logo')}}</span>
        <input class="form-control" type="file" name="app_logo" id="change-app-logo" hidden accept="image/*" name="app_logo" onclick="showImagePreview('change-app-logo','app-logo-preview',512,512);"/>
        <span class="d-block d-sm-none">
            <i class="me-0" data-feather="edit"></i>
        </span>
        </label>
        <p>{{__('lang.admin_app_logo_resolution')}}</p>
    </div>
    </div>
</div>
@endif
@if($row->key == 'rectangualr_app_logo')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_rectangualr_app_logo')}}</label>
    <div class="d-flex">
    <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="app-rectangular-logo-preview" alt="rectangualr_app_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
    <div class="mt-75 ms-1">
        <label class="btn btn-primary me-75 mb-0" for="change-rectangular-logo">
        <span class="d-none d-sm-block">{{__('lang.admin_upload_rectangualr_app_logo')}}</span>
        <input class="form-control" type="file" name="rectangualr_app_logo" id="change-rectangular-logo" hidden accept="image/*" name="rectangualr_app_logo" onclick="showImagePreview('change-rectangular-logo','app-rectangular-logo-preview',379,128);"/>
        <span class="d-block d-sm-none">
            <i class="me-0" data-feather="edit"></i>
        </span>
        </label>
        <p>{{__('lang.admin_rectangualr_app_logo_resolution')}}</p>
    </div>
    </div>
</div>
@endif
@if($row->key == 'app_splash_screen')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_app_splash_screen')}}</label>
    <div class="d-flex">
    <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="app-splash-screen-preview" alt="app_splash_screen" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
    <div class="mt-75 ms-1">
        <label class="btn btn-primary me-75 mb-0" for="change-app-splash-screen">
        <span class="d-none d-sm-block">{{__('lang.admin_upload_app_splash_screen')}}</span>
        <input class="form-control" type="file" name="app_splash_screen" id="change-app-splash-screen" hidden accept="image/*" name="app_splash_screen" onclick="showImagePreview('change-app-splash-screen','app-splash-screen-preview',1000,1000);"/>
        <span class="d-block d-sm-none">
            <i class="me-0" data-feather="edit"></i>
        </span>
        </label>
        <p>{{__('lang.admin_app_splash_screen_resolution')}}</p>
    </div>
    </div>
</div>
@endif
