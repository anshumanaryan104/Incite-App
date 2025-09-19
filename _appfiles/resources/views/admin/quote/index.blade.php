@extends('admin/layout/app') @section('content') 
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> Quotes {{__('lang.admin_list')}}
  </h4>
  <button class="btn btn-secondary btn-primary float-right mt-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#add-new-record" aria-controls="add-new-record">
    <span>
      <i class="ti ti-plus me-md-1"></i>
      <span class="d-md-inline-block d-none">Create Quotes</span>
    </span>
  </button>
  <div class="card margin-bottom-20">
    <div class="card-header">
      <form method="get">
        <div class="row">
          <h5 class="card-title display-inline-block">Filters</h5>
          <div class="form-group col-sm-3 display-inline-block" >
              <input type="text" class="form-control" placeholder="Search quotes" name="quote" value="@if(isset($_GET['quote']) && $_GET['quote']!=''){{$_GET['quote']}}@endif">
          </div>
          <div class="col-sm-3 display-inline-block">
              <select class="form-control" name="status">
                <option value="">Select Status</option> 
                <option value="0" @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status']==0) selected @endif @endif>Inactive</option>
                <option value="1" @if(isset($_GET['status']) && $_GET['status']!='') @if($_GET['status']==1) selected @endif @endif>Active</option>
              </select>
          </div>
          <div class="col-sm-3 display-inline-block">
            <button type="submit" class="btn btn-primary data-submit">Search</button>
            <a type="reset" class="btn btn-outline-secondary" href="{{url('admin/quotes')}}">Reset</a>
          </div>
        </div>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block">Quotes {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive text-nowrap"> @include('admin/quote/table') </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>
  <div class="offcanvas offcanvas-end" id="add-new-record">
    <div class="offcanvas-header border-bottom">
      <h5 class="offcanvas-title" id="exampleModalLabel">Add Quotes</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
      <form class="add-new-record pt-0 row g-2" id="add-record" onsubmit="return validateQuotes('add-record');" action="{{url('admin/add-quote')}}" method="POST"> @csrf 
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="quote">Quote <span class="required">*</span></label>
            <textarea class="form-control" id="quote" name="quote" placeholder="Enter quote"></textarea>
          </div>
        </div>
        <div class="col-sm-12">
          <div class="mb-1">
            <label class="form-label" for="schedule_date">Schedule Date <span class="required">*</span></label>
            <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM"  name="schedule_date" readonly="readonly">
          </div>
        </div>
        
        <div class="col-sm-12">
          <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">Add</button>
          <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div> @endsection