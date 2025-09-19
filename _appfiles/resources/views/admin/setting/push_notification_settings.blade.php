@if($row->key == 'enable_notifications')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="enable_notifications" id="enable_notifications" @if($row->value == 1) checked @endif >
        <label class="form-check-label" for="enable_notifications">{{__('lang.admin_enable_push_notification_placeholder')}}</label>
    </div>
</div>
@endif
<!-- @if($row->key == 'firebase_msg_key')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="firebase_msg_key">{{__('lang.admin_firebase_msg_key')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" name="firebase_msg_key" placeholder="{{__('lang.admin_firebase_msg_key_placeholder')}}">
</div>
@endif
@if($row->key == 'firebase_api_key')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="firebase_api_key">{{__('lang.admin_firebase_api_key')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" name="firebase_api_key" placeholder="{{__('lang.admin_firebase_api_key_placeholder')}}">
</div>
@endif -->
@if($row->key == 'one_signal_key')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="one_signal_key">{{__('lang.admin_one_signal_key')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" name="one_signal_key" placeholder="{{__('lang.admin_one_signal_key_placeholder')}}">
</div>
@endif
@if($row->key == 'one_signal_app_id')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="one_signal_app_id">{{__('lang.admin_one_signal_app_id')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" name="one_signal_app_id" placeholder="{{__('lang.admin_one_signal_app_id_placeholder')}}">
</div>
@endif