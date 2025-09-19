<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_url')}}</th>
            @can('update-social-media-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-social-media', 'delete-social-media'])
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
                        @if (Gate::check('update-social-media'))
                            @if(isset($row->name) && $row->name!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->name}}</a>@else -- @endif
                        @else
                            @if($row->name!=''){{$row->name}}@else -- @endif
                        @endif     
                    </td>
                    <td>@if(isset($row->url) && $row->url!=''){{$row->url}}@else -- @endif</td>
                    @can('update-social-media-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-social-media-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-social-media-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['update-social-media', 'delete-social-media'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-social-media')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('delete-social-media')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-social-media', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_social_media_link')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record_{{$row->id}}" action="{{url('admin/update-social-media')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateSocialMedia('edit-record_{{$row->id}}');"> @csrf 
                                    <input type="hidden" name="id" value="{{$row->id}}">
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="name" placeholder="{{__('lang.admin_name_placeholder')}}" name="name" value="{{$row->name}}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="url">{{__('lang.admin_url')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="url" placeholder="{{__('lang.admin_url_placeholder')}}" name="url" value="{{$row->url}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="icon">{{__('lang.admin_icon')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="icon" placeholder="{{__('lang.admin_icon_placeholder')}}" name="icon"  value="{{$row->icon}}" readonly>
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
                <td colspan="7" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}s</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>