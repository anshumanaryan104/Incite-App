@extends('admin/layout/app') @section('content') <div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> {{__('lang.admin_personalization')}} {{__('lang.admin_list')}}
  </h4>
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block"> {{__('lang.admin_personalization')}} {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive text-nowrap"> 
      <table class="table">
          <thead class="table-light">
              <tr class="text-nowrap">
                  <th>{{__('lang.admin_id')}}</th>
                  <th>{{__('lang.admin_category')}}</th>
                  <th>{{__('lang.admin_added_on_date')}}</th>
              </tr>
          </thead>
          <tbody>    
              @php $i=0; @endphp 
              @if(count($result) > 0) 
                  @foreach($result as $row) 
                      @php $i++; @endphp 
                      <tr>
                          <td>{{$i}}</td>
                          <td>@if(isset($row->category->name) && $row->category->name!=''){{$row->category->name}}@else--@endif</td>
                          <td>{{date(setting('date_format'),strtotime($row->created_at))}}</td> 
                      </tr> 
                  @endforeach 
              @else 
                  <tr>
                      <td colspan="9" class="record-not-found">
                          <span>{{__('lang.admin_record_not_found')}}</span>
                      </td>
                  </tr> 
              @endif 
          </tbody>
      </table>
    </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>
</div> @endsection