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