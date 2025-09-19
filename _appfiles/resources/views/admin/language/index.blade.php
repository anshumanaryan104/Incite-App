@extends('admin/layout/app') @section('content') 
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> {{__('lang.admin_languages')}} {{__('lang.admin_list')}}
  </h4>
  @can('add-language')
  <button class="btn btn-secondary btn-primary float-right mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#add-new-record" aria-controls="add-new-record">
    <span>
      <i class="ti ti-plus me-md-1"></i>
      <span class="d-md-inline-block d-none">{{__('lang.admin_create_language')}}</span>
    </span>
  </button>
  @endcan
  <div class="card margin-bottom-20">
    <div class="card-header">
      <form method="get">
        <div class="row">
          <h5 class="card-title display-inline-block">{{__('lang.admin_filters')}}</h5>
          <div class="form-group col-sm-3 display-inline-block" >
              <input type="text" class="form-control dt-full-name" placeholder="{{__('lang.search_name')}}" name="search" value="@if(isset($_GET['search']) && $_GET['search']!=''){{$_GET['search']}}@endif">
          </div>
          <div class="col-sm-3 display-inline-block">
              <select class="form-control" name="status">
                <option value="">{{__('lang.admin_select_status')}}</option> 
                <option value="0" @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status']==0) selected @endif @endif>{{__('lang.admin_inactive')}}</option>
                <option value="1" @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status']==1) selected @endif @endif>{{__('lang.admin_active')}}</option>
              </select>
          </div>
          <div class="col-sm-3 display-inline-block">
            <button type="submit" class="btn btn-primary data-submit">{{__('lang.admin_search')}}</button>
            <a type="reset" class="btn btn-outline-secondary" href="{{url('admin/languages')}}">{{__('lang.admin_reset')}}</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block">{{__('lang.admin_languages')}} {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive text-nowrap"> @include('admin/language/table') </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>
  <div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_add_language')}}</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
      <form class="add-new-record pt-0 row g-2" id="add-record" onsubmit="return  ('add-record');" action="{{url('admin/add-language')}}" method="POST"> @csrf 
        <!-- <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
            <input class="form-control" id="name" name="name" placeholder="{{__('lang.admin_name_placeholder')}}"/>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="code">{{__('lang.admin_code')}} <span class="required">*</span></label>
            <input class="form-control" id="code" name="code" placeholder="{{__('lang.admin_code_placeholder')}}"/>
          </div>
        </div> -->
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="code_id">{{__('lang.admin_language_code')}} <span class="required">*</span></label>
              <select id="multicol-country" class="select2 form-control" name="code_id">
                <option value="">{{__('lang.admin_select_language_code')}} </option>
                @if(count($code)) 
                  @foreach($code as $code_data)
                    <option value="{{$code_data->id}}">{{$code_data->code}} ({{$code_data->name}})</option>  
                  @endforeach
                @endif
              </select>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="position">{{__('lang.admin_position')}} <span class="required">*</span></label>
              <select class="form-control" name="position">
                <option value="">{{__('lang.admin_select_position')}} </option> 
                <option value="ltr">LTR</option> 
                <option value="rtl">RTL</option> 
              </select>
          </div>
        </div>
        <div class="col-md-12">
            <label class="switch switch-square">
                <input type="checkbox" class="switch-input" id="is_default" name="is_default">
                <span class="switch-toggle-slider">
                    <span class="switch-on"></span>
                    <span class="switch-off"></span>
                </span>
                <span class="switch-label">{{__('lang.admin_is_default')}}</span>
            </label>
        </div>
        <!-- <div class="col-md-12 mb-3 display-inline-block width-32-percent mr-10">
            <div class="form-check form-check-primary mt-3">
                <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                <label class="form-check-label" for="is_default">{{__('lang.admin_is_default_placeholder')}}</label>
            </div>
        </div> -->
        <div class="col-sm-12">
          <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">{{__('lang.admin_button_save_changes')}}</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{__('lang.admin_button_cancel')}}</button>
        </div>
      </form>
    </div>
  </div>
</div> @endsection