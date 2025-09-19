@extends('admin/layout/app') @section('content') <div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> {{__('lang.admin_cms')}} {{__('lang.admin_list')}}
  </h4>
  @can('add-cms')
  <a class="btn btn-secondary btn-primary float-right mt-3" href="{{url('admin/add-cms')}}">
    <span>
      <i class="ti ti-plus me-md-1"></i>
      <span class="d-md-inline-block d-none">{{__('lang.admin_create_cms')}}</span>
    </span>
  </a>
  @endcan
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block">{{__('lang.admin_cms')}} {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive"> @include('admin/cms/table') </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>

</div> @endsection