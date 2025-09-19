@if($row->key == 'enable_maintainance_mode')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="enable_maintainance_mode" id="enable_maintainance_mode" @if($row->value == 1) checked @endif>
        <label class="form-check-label" for="enable_maintainance_mode">{{__('lang.admin_enable_maintainance_mode_placeholder')}}</label>
    </div>
</div>
@endif
@if($row->key == 'maintainance_title')
<div class="col-md-5 mb-3 display-inline-block">
    <label class="form-label" for="maintainance_title">{{__('lang.admin_maintainance_title')}}</label>
    <input type="text" class="form-control" name="maintainance_title" value="{{$row->value}}" placeholder="{{__('lang.admin_maintainance_title_placeholder')}}">
</div>
@endif
@if($row->key == 'maintainance_short_text')
<div class="col-md-6 mb-3 display-inline-block">
    <label class="form-label" for="maintainance_short_text">{{__('lang.admin_maintainance_short_text')}}</label>
    <input type="text" class="form-control" name="maintainance_short_text" value="{{$row->value}}" placeholder="{{__('lang.admin_maintainance_short_text_placeholder')}}">
</div>
@endif