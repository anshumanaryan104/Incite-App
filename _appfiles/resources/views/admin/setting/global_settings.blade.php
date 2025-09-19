
@if($row->key == 'date_format')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="date_format">{{__('lang.admin_date_format')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_date_format_placeholder')}}" name="date_format"/>
</div>
@endif
@if($row->key == 'timezone')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="timezone">{{__('lang.admin_timezone')}}</label>
    <select name="timezone" class="form-control">
        <option value="">{{__('lang.admin_select_timezone')}}</option>
        @for($c= 0; $c< count($zones);$c++)
            <option @if($row->value == $zones[$c]) selected  @endif value="{{$zones[$c]}}">{{$zones[$c]}}</option>
        @endfor
    </select>
</div>
@endif