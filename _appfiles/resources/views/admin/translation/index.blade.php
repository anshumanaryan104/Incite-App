@extends('admin/layout/app') @section('content') 
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> {{__('lang.admin_translation')}} {{__('lang.admin_list')}}
  </h4>
  @can('add-translation')
  <button class="btn btn-secondary btn-primary float-right mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#add-new-record" aria-controls="add-new-record">
    <span>
      <i class="ti ti-plus me-md-1"></i>
      <span class="d-md-inline-block d-none">{{__('lang.admin_create_translation')}}</span>
    </span>
  </button>
  @endcan
  <div class="card margin-bottom-20">
    <div class="card-header">
      <form method="get">
        <div class="row">
          <h5 class="card-title display-inline-block">{{__('lang.admin_filters')}}</h5>
          <div class="form-group col-sm-3 display-inline-block" >
              <input type="text" class="form-control" placeholder="{{__('lang.admin_value')}}" name="value" value="@if(isset($_GET['value']) && $_GET['value']!=''){{$_GET['value']}}@endif">
          </div>
          <div class="col-sm-3 display-inline-block">
              <select class="form-control" name="group">
                <option value="">{{__('lang.admin_select_group')}}</option> 
                <option value="frontend" @if(isset($_GET['group']) && $_GET['group']!='') @if($_GET['group'] == 'frontend') selected @endif @endif>Website</option> 
                <option value="admin" @if(isset($_GET['group']) && $_GET['group']!='') @if($_GET['group'] == 'admin') selected @endif @endif>Admin</option> 
                <option value="api" @if(isset($_GET['group']) && $_GET['group']!='') @if($_GET['group'] == 'api') selected @endif @endif>API</option> 
                <option value="message_alerts" @if(isset($_GET['group']) && $_GET['group']!='') @if($_GET['group'] == 'message_alerts') selected @endif @endif>Message Alerts</option> 
              </select>
          </div>
          <div class="col-sm-3 display-inline-block">
              <select class="form-control" name="language_id">
                <option value="">{{__('lang.admin_language')}}</option> 
                @if(isset($languages) && count($languages))
                  @foreach($languages as $language)
                    <option value="{{$language->id}}" @if(isset($_GET['language_id']) && $_GET['language_id']!='') @if($language->id == $_GET['language_id']) selected @endif @endif>{{$language->name}}</option> 
                  @endforeach
                @endif
              </select>
          </div>
          <div class="col-sm-3 display-inline-block">
            <button type="submit" class="btn btn-primary data-submit">{{__('lang.admin_search')}}</button>
            <a type="reset" class="btn btn-outline-secondary" href="{{url('admin/translation')}}">{{__('lang.admin_reset')}}</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block">{{__('lang.admin_translation')}} {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive text-nowrap"> @include('admin/translation/table') </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>
  <div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_add_translation')}}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
      <form class="add-new-record pt-0 row g-2" id="add-record" onsubmit="return validateTranslation('add-record');" action="{{url('admin/add-translation')}}" method="POST"> @csrf 
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="group">Group <span class="required">*</span></label>
              <select class="form-control" name="group">
                <option value="">{{__('lang.admin_group')}}</option> 
                <option value="frontend">Website</option> 
                <option value="admin">Admin</option> 
                <option value="api">API</option> 
                <option value="message_alerts">Message Alerts</option> 
              </select>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="keyword">{{__('lang.admin_keyword')}} <span class="required">*</span></label>
            <input class="form-control" id="keyword" name="keyword" placeholder="{{__('lang.admin_keyword_placeholder')}}"/>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="value">{{__('lang.admin_value')}} <span class="required">*</span></label>
            <input class="form-control" id="value" name="value" placeholder="{{__('lang.admin_value_placeholder')}}"/>
          </div>
        </div>
        
        <div class="col-sm-12">
          <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">{{__('lang.admin_button_save_changes')}}</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{__('lang.admin_button_cancel')}}</button>
        </div>
      </form>
    </div>
  </div>
</div> @endsection