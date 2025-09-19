@if($row->key == 'mailer')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="mailer">{{__('lang.admin_mailer')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_mailer_placeholder')}}"  name="mailer" value="{{$row->value}}"/>
</div>
@endif
@if($row->key == 'host')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="host">{{__('lang.admin_host')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_host_placeholder')}}"  name="host" value="{{$row->value}}"/>
</div>
@endif
@if($row->key == 'port')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="port">{{__('lang.admin_port')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_port_placeholder')}}"  name="port" value="{{$row->value}}"/>
</div>
@endif     
@if($row->key == 'username')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="username">{{__('lang.admin_username')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_username_placeholder')}}"  name="username" value="{{$row->value}}"/>
</div>
@endif
@if($row->key == 'password')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="password">{{__('lang.admin_password')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_password_placeholder')}}"  name="password" value="{{$row->value}}"/>
</div>
@endif
@if($row->key == 'encryption')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="encryption">{{__('lang.admin_encryption')}}</label>
    <input type="text" class="form-control" placeholder="{{__('lang.admin_encryption_placeholder')}}"  name="encryption" value="{{$row->value}}"/>
</div>
@endif
@if($row->key == 'from_name')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10">
    <label class="form-label" for="from_name">{{__('lang.admin_from_name')}}</label>
    <input type="text" class="form-control" name="from_name" placeholder="{{__('lang.admin_from_name_placeholder')}}" value="{{$row->value}}">
</div>
@endif