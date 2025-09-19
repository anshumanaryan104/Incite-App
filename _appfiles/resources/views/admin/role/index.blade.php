@extends('admin/layout/app') @section('content') 
<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4 display-inline-block">
    <span class="text-muted fw-light">
      <a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> / </span> {{__('lang.admin_roles')}} {{__('lang.admin_list')}}
  </h4>
  @can('add-role')
  <button class="btn btn-secondary btn-primary float-right mt-3" type="button" href="javascript:;" data-bs-toggle="modal" data-bs-target="#addRoleModal" class="role-edit-modal">
    <span>
      <i class="ti ti-plus me-md-1"></i>
      <span class="d-md-inline-block d-none">{{__('lang.admin_create_role')}}</span>
    </span>
  </button>
  @endcan
  <div class="card">
    <div class="card-header">
      <h5 class="card-title display-inline-block">{{__('lang.admin_roles')}} {{__('lang.admin_list')}}</h5>
      <h6 class="float-right"> <?php if ($result->firstItem() != null) {?> {{__('lang.admin_showing')}} {{ $result->firstItem() }}-{{ $result->lastItem() }} {{__('lang.admin_of')}} {{ $result->total() }} <?php }?> </h6>
    </div>
    <div class="table-responsive text-nowrap"> @include('admin/role/table') </div>
    <div class="card-footer">
      <div class="pagination" style="float: right;">
        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
      </div>
    </div>
  </div>
  <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
      <div class="modal-content p-3 p-md-5">
        <button
          type="button"
          class="btn-close btn-pinned"
          data-bs-dismiss="modal"
          aria-label="Close"
        ></button>
        <div class="modal-body">
          <div class="text-center mb-4">
            <h3 class="role-title mb-2">{{__('lang.admin_add_role')}}</h3>
            <p class="text-muted">{{__('lang.admin_set_role_permission')}}</p>
          </div>
          <!-- Add role form -->
          <form class="row g-3" id="add-record" onsubmit="return validateRole('add-record');" action="{{url('admin/add-role')}}" method="POST">
            @csrf
            <div class="col-12 mb-4">
              <label class="form-label" for="name"> {{__('lang.admin_role_name')}} <span class="required">*</span></label>
              <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                placeholder="{{__('lang.admin_role_name_placeholder')}}"
                tabindex="-1"
              />
            </div>
            <div class="col-12">
              <h5>{{__('lang.admin_role_permissions')}}</h5>
              <div class="d-flex">
                <div class="form-check me-3 me-lg-5 permission">
                  <input class="form-check-input permission-all-checkbox_List" type="checkbox" value="List" data-permission="List" onclick="selectAllSameData('permission-all-checkbox_List','permission-checkbox_List');"/>
                  <label class="form-check-label" for="All"> All List</label>
                </div>
                <div class="form-check me-3 me-lg-5 permission">
                  <input class="form-check-input permission-all-checkbox_Add" type="checkbox" value="Add" data-permission="Add" onclick="selectAllSameData('permission-all-checkbox_Add','permission-checkbox_Add');"/>
                  <label class="form-check-label" for="Add"> All Add</label>
                </div>
                <div class="form-check me-3 me-lg-5 permission">
                  <input class="form-check-input permission-all-checkbox_Update" type="checkbox" value="Update" data-permission="Update" onclick="selectAllSameData('permission-all-checkbox_Update','permission-checkbox_Update');"/>
                  <label class="form-check-label" for="Update"> All Update</label>
                </div>
                <div class="form-check me-3 me-lg-5 permission">
                  <input class="form-check-input permission-all-checkbox_Status Change" type="checkbox" data-permission="Status" onclick="selectAllSameData('permission-all-checkbox_Status','permission-checkbox_Status');"/>
                  <label class="form-check-label" for="Status"> All Status Change</label>
                </div>
                <div class="form-check me-3 me-lg-5 permission">
                  <input class="form-check-input permission-all-checkbox_Delete" type="checkbox" value="Delete" data-permission="Delete" onclick="selectAllSameData('permission-all-checkbox_Delete','permission-checkbox_Delete');"/>
                  <label class="form-check-label" for="Delete"> All Delete</label>
                </div>
              </div>
              <!-- Permission table -->
              <div class="table-responsive" style="margin-top: 20px;">
                <table class="table table-flush-spacing">
                  <tbody>
                    @foreach($permission as $value)
                    <tr>
                      <td class="text-nowrap fw-semibold">@if($value->module=='Blog')Blog/Quote @else {{$value->module}} @endif</td>
                      <td>
                        <div class="d-flex">
                          @foreach($value->permission as $detail)
                          <div class="form-check me-3 me-lg-5 permission">
                            @if($detail->permission_name=='Status Change')
                              <input class="form-check-input permission-checkbox_Status" type="checkbox" id="{{ $detail->route_name }}" name="permission[]" value="{{$detail->id}}" {{$detail->is_default ? 'checked' : ''}} data-permission="{{ $detail->permission_name }}" />
                            @else
                              <input class="form-check-input permission-checkbox_{{ $detail->permission_name }}" type="checkbox" id="{{ $detail->route_name }}" name="permission[]" value="{{$detail->id}}" {{$detail->is_default ? 'checked' : ''}} data-permission="{{ $detail->permission_name }}" />
                            @endif
                            <label class="form-check-label" for="{{ $detail->route_name }}"> {{ $detail->permission_name }} </label>
                          </div>
                          @endforeach
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
              <!-- Permission table -->
            </div>
            <div class="col-12 text-center mt-4">
              <button type="submit" class="btn btn-primary me-sm-3 me-1">{{__('lang.admin_button_save_changes')}}</button>
              <button
                type="reset"
                class="btn btn-label-secondary"
                data-bs-dismiss="modal"
                aria-label="Close"
              >
              {{__('lang.admin_button_cancel')}}
              </button>
            </div>
          </form>
          <!--/ Add role form -->
        </div>
      </div>
    </div>
  </div>
</div> 
@endsection