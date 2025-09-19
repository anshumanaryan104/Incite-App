@if($row->key == 'enable_google_login')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="enable_google_login" id="enable_google_login" @if($row->value == 1) checked @endif >
        <label class="form-check-label" for="enable_google_login">{{__('lang.admin_enable_google_login_placeholder')}}</label>
    </div>
</div>
@endif
@if($row->key == 'google_client_id')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="google_client_id">{{__('lang.admin_google_client_id')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_google_client_id_placeholder')}}" name="google_client_id"/>
</div>
@endif
@if($row->key == 'google_client_secret')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="google_client_secret">{{__('lang.admin_google_client_secret')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_google_client_secret_placeholder')}}" name="google_client_secret"/>
</div>
@endif