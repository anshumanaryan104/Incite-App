<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_email')}}</th>
            <th>{{__('lang.admin_created_at')}}</th>
            @can('update-user-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-sub-admin', 'delete-user'])
            <th>{{__('lang.admin_action')}}</th>
            @endcanany
        </tr>
    </thead>
    <tbody>    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr>
                    <td>{{$i}}</td>
                    <td>
                        <img src="{{ url('uploads/user/'.$row->photo)}}" class="me-75" height="50" width="50" alt="{{$row->name}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                    </td>
                    <td>
                        @if (Gate::check('update-sub-admin'))
                            <a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->name}}</a>
                        @else
                            @if($row->name!=''){{$row->name}}@else -- @endif
                        @endif     
                    </td>
                    <td>{{$row->email}}</td>
                    <td>{{$row->created_at}}</td>
                    @can('update-user-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-user-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-user-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['update-sub-admin', 'delete-user'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-sub-admin')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('delete-user')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-user', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_subadmin')}} </h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record_{{$row->id}}" action="{{url('admin/update-sub-admin')}}" method="POST" enctype="multipart/form-data" > @csrf 
                                    <input type="hidden" name="id" value="{{$row->id}}">
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="name" placeholder="{{__('lang.admin_name_placeholder')}}" name="name" value="{{$row->name}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="email">{{__('lang.admin_email')}} <span class="required">*</span></label>
                                            <input type="email" class="form-control" id="email" placeholder="{{__('lang.admin_email_placeholder')}}" name="email" value="{{$row->email}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="role_id">{{__('lang.admin_role')}} <span class="required">*</span></label>
                                            <select class="form-control" id="role_id" name="role_id">
                                            <option value="">{{__('lang.admin_select_role')}}</option>
                                            @if(isset($roles) && count($roles))
                                                @foreach($roles as $role)
                                                <option @if($row->role_id==$role->id) selected @endif value="{{$role->id}}">{{$role->name}}</option>
                                                @endforeach
                                            @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="password">{{__('lang.admin_password')}}</label>
                                            <input type="password" class="form-control" id="password" placeholder="{{__('lang.admin_password_placeholder')}}" name="password">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_image')}}</label>
                                            <div class="d-flex">
                                                <img src="{{ url('uploads/user/'.$row->photo)}}" class="rounded me-50" id="image-preview" alt="photo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
                                                <div class="mt-75 ms-1">
                                                    <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                                    <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                                    <input class="form-control" type="file" name="photo" id="change-picture" hidden accept="image/*" name="photo" onclick="showImagePreview('change-picture','image-preview',512,512);"/>
                                                    <span class="d-block d-sm-none">
                                                        <i class="me-0" data-feather="edit"></i>
                                                    </span>
                                                    </label>
                                                    <p>{{__('lang.admin_subadmin_image_resolution')}}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button type="submit" class="btn btn-primary data-submit me-sm-3 me-1">{{__('lang.admin_button_save_changes')}}</button>
                                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">{{__('lang.admin_button_cancel')}}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                    @endcanany
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