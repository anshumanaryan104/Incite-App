<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_pdf')}}</th>
            @can('update-e-paper-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-e-paper', 'delete-e-paper', 'translation-e-paper'])
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
                        <img src="{{ url('uploads/e-paper/'.$row->image)}}" class="me-75" height="50" width="50" alt="{{$row->name}}" onerror="this.onerror=null;this.src=`{{ asset('upload/no-image.png') }}`" />
                    </td>
                    <td>
                        @if (Gate::check('update-e-paper'))
                            @if($row->name!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->name}}</a>@else -- @endif
                        @else
                            @if($row->name!=''){{$row->name}}@else -- @endif
                        @endif    
                    </td>
                    <td>
                        @if($row->pdf!='')
                            <a target="_blank" class="rounded me-50" href="{{ asset('uploads/e-paper/pdf')}}/{{$row->pdf}}"><i class="ti ti-download me-1 margin-top-negative-4"></i></a>
                        @else
                            --
                        @endif
                    </td>
                    @can('update-e-paper-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-e-paper-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-e-paper-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['update-e-paper', 'delete-e-paper', 'translation-e-paper'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-e-paper')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('translation-e-paper')
                                <a class="dropdown-item" href="{{url('/admin/translation-e-paper/'.$row->id)}}">
                                <i class="ti ti-language me-1 margin-top-negative-4"></i> {{__('lang.admin_translation')}} </a>
                                @endcan
                                @can('delete-e-paper')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-e-paper', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="Delete">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_epaper')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record" action="{{url('admin/update-e-paper')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateEpaper();"> @csrf 
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <input type="hidden" name="id" value="{{$row->id}}">
                                            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control" id="name" placeholder="{{__('lang.admin_name_placeholder')}}" name="name" value="{{$row->name}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_pdf')}}</label>
                                            <div class="d-flex">
                                                @if($row->pdf!='')
                                                <a target="_blank" class="rounded me-50" href="{{ asset('uploads/e-paper/pdf')}}/{{$row->pdf}}"><i class="ti ti-download me-1 margin-top-negative-4"></i></a>
                                                @endif
                                                <div class="mt-75 ms-1">
                                                    <label class="btn btn-primary me-75 mb-0" for="change-pdf_{{$row->id}}">
                                                    <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                                    <input class="form-control" type="file" name="pdf" id="change-pdf_{{$row->id}}" hidden accept=".pdf" name="pdf" />
                                                    <span class="d-block d-sm-none">
                                                        <i class="me-0" data-feather="edit"></i>
                                                    </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_image')}} <span class="required">*</span></label>
                                        <div class="d-flex">
                                            <img src="{{ asset('uploads/e-paper')}}/{{$row->image}}" id="image-preview_{{$row->id}}" class="rounded me-50" alt="profile image" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('upload/no-image.png') }}`" />
                                            <div class="mt-75 ms-1">
                                            <label class="btn btn-primary me-75 mb-0" for="change-picture_{{$row->id}}">
                                                <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                                <input class="form-control" type="file" id="change-picture_{{$row->id}}" hidden accept="image/png, image/jpeg, image/jpg" name="image" onclick="showImagePreview('change-picture_{{$row->id}}','image-preview_{{$row->id}}',512,512);"/>
                                                <span class="d-block d-sm-none">
                                                <i class="me-0" data-feather="edit"></i>
                                                </span>
                                            </label>
                                            <p>{{__('lang.admin_epaper_image_resolution')}}</p>
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
                <td colspan="7" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>