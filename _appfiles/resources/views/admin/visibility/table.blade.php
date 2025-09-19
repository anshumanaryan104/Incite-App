<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_visibility')}}</th>
            @can('update-visibility-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-visibility', 'delete-visibility', 'translation-visibility'])
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
                        @if (Gate::check('update-visibility'))
                            @if($row->display_name!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->display_name}}</a>@else -- @endif
                        @else
                            @if($row->display_name!=''){{$row->display_name}}@else -- @endif
                        @endif 
                    </td>
                    @can('update-visibility-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-visibility-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-visibility-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endif
                    @canany(['update-visibility', 'delete-visibility', 'translation-visibility'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-visibility')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('translation-visibility')
                                <a class="dropdown-item" href="{{url('/admin/translation-visibility/'.$row->id)}}">
                                <i class="ti ti-language me-1 margin-top-negative-4"></i> {{__('lang.admin_translation')}}   </a>
                                @endif
                                @can('delete-visibility')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-visibility', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="Delete">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_visibillity')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record" action="{{url('admin/update-visibility')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateVisibility();"> @csrf 
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <input type="hidden" name="id" value="{{$row->id}}">
                                            <label class="form-label" for="display_name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="display_name" placeholder="{{__('lang.admin_name_placeholder')}}" name="display_name" value="{{$row->display_name}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-check form-check-primary mt-3">
                                            <input class="form-check-input" type="checkbox" name="is_website" id="is_website"  @if($row->is_website==1) checked="" @endif>
                                            <label class="form-check-label" for="is_website">{{__('lang.admin_is_website')}}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-check form-check-primary mt-3">
                                            <input class="form-check-input" type="checkbox" name="is_app" id="is_app" @if($row->is_app==1) checked="" @endif>
                                            <label class="form-check-label" for="is_app">{{__('lang.admin_is_app')}}</label>
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
                    <span>Record not found</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>