<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_code')}}</th>
            <th>{{__('lang.admin_position')}}</th>
            @can('update-language-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            <th>{{__('lang.admin_default')}}</th>
            @canany(['update-language', 'delete-language'])
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
                        @if (Gate::check('update-language'))
                            @if($row->name!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->name}}</a>@else -- @endif
                        @else
                            @if($row->name!=''){{$row->name}}@else -- @endif
                        @endif  
                    </td>
                    <td>@if($row->code!=''){{$row->code}}@else -- @endif</td>
                    <td>@if($row->position!=''){{$row->position}}@else -- @endif</td>
                    @can('update-language-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-language-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-language-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    <td> 
                        @if($row->is_default==1) 
                            <span class="badge bg-success">{{__('lang.admin_yes')}}</span>
                        @else 
                            <span class="badge bg-danger">{{__('lang.admin_no')}}</span>
                        @endif 
                    </td>
                    @canany(['update-language', 'delete-language'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-language')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('delete-language')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-language', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_language')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record" action="{{url('admin/update-language')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateLanguage();"> @csrf 
                                    <input type="hidden" name="id" value="{{$row->id}}"/>
                                    <!-- <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input class="form-control" id="name" name="name" placeholder="{{__('lang.admin_name_placeholder')}}" value="{{$row->name}}"/>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="code">{{__('lang.admin_code')}} <span class="required">*</span></label>
                                            <input class="form-control" id="code" name="code" placeholder="{{__('lang.admin_code_placeholder')}}" value="{{$row->code}}"/>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="code_id">{{__('lang.admin_language_code')}} <span class="required">*</span></label>
                                            <select id="multicol-country" class="select2 form-control" name="code_id">
                                                <option value="">{{__('lang.admin_select_language_code')}} </option>
                                                @if(count($code)) 
                                                @foreach($code as $code_data)
                                                    <option value="{{$code_data->id}}" @if(isset($row) && $row!='') @if($row->code_id==$code_data->id) selected @endif @endif>{{$code_data->code}} ({{$code_data->name}})</option>  
                                                @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="position">{{__('lang.admin_position')}} <span class="required">*</span></label>
                                            <select class="form-control" name="position">
                                                <option value="">{{__('lang.admin_select_position')}}</option> 
                                                <option value="ltr" @if(isset($row) && $row!='') @if($row->position=='ltr') selected @endif @endif>LTR</option> 
                                                <option value="rtl" @if(isset($row) && $row!='') @if($row->position=='rtl') selected @endif @endif>RTL</option> 
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="switch switch-square">
                                            <input type="checkbox" class="switch-input" id="is_default" name="is_default" @if(isset($row) && $row!='') @if($row->is_default==1) checked @endif @endif>
                                            <span class="switch-toggle-slider">
                                                <span class="switch-on"></span>
                                                <span class="switch-off"></span>
                                            </span>
                                            <span class="switch-label">{{__('lang.admin_is_default')}}</span>
                                        </label>
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
                <td colspan="6" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>