<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_main_category')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_total_blogs')}}</th>
            @can('update-category-column')
            <th>{{__('lang.admin_featured')}}</th>
            @endcan
            @can('update-category-column')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-category', 'delete-category', 'translation-category'])
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
                        <img src="{{ url('uploads/category/'.$row->image)}}" class="me-75" height="50" width="50" alt="{{$row->name}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                    </td>
                    <td>
                        @if (Gate::check('update-category'))
                            @if(isset($row->main_category) && $row->main_category!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->main_category->id}}" aria-controls="edit-new-record_{{$row->main_category->id}}">{{$row->main_category->name}}</a>@else--@endif
                        @else
                            @if(isset($row->main_category) && $row->main_category!=''){{$row->main_category->name}}@else--@endif
                        @endif
                    </td>
                    <td>
                        @if (Gate::check('update-category'))
                            <a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->name}}</a>
                        @else
                            {{$row->name}}
                        @endif
                    </td>
                    <td>
                        @if (Gate::check('update-category'))
                            <a href="{{url('admin/blog?category_id='.$row->id)}}">{{$row->blog_count}}</a>
                        @else
                            {{$row->blog_count}}
                        @endif
                    </td>
                    @can('update-category-column')
                    <td> @if($row->is_featured==1) 
                        <a href="{{url('admin/update-category-column/'.$row->id.'/is_featured/0')}}" title="{{__('lang.admin_yes')}}">
                        <span class="badge bg-success">{{__('lang.admin_yes')}}</span>
                        </a> @else <a href="{{url('admin/update-category-column/'.$row->id.'/is_featured/1')}}" title="{{__('lang.admin_no')}}">
                        <span class="badge bg-danger">{{__('lang.admin_no')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @can('update-category-column')
                    <td> @if($row->status==1) <a href="{{url('admin/update-category-column/'.$row->id.'/status/0')}}" title="{{__('lang.admin_active')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-category-column/'.$row->id.'/status/1')}}" title="{{__('lang.admin_inactive')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['update-category', 'delete-category', 'translation-category'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" title="{{__('lang.admin_select_action')}}">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-category')
                                <a class="dropdown-item" type="button" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}" title="{{__('lang.admin_edit')}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('translation-category')
                                <a class="dropdown-item" href="{{url('/admin/translation-category/'.$row->id)}}" title="{{__('lang.admin_translation')}}">
                                <i class="ti ti-language me-1 margin-top-negative-4"></i> {{__('lang.admin_translation')}} </a>
                                @endcan
                                @can('delete-category')
                                <form id="deleteForm_{{$row->id}}" action="{{ url('admin/delete-category', $row->id) }}" method="POST" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_category')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record" action="{{url('admin/update-category')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateCategory();"> @csrf 
                                    @if($row->parent_id!=0)
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_main_category')}}</label>
                                            <select class="form-control dt-uname" name="parent_id">
                                                <option value="">{{__('lang.admin_select_main_category')}}</option> @if(isset($categories) && count($categories)) @foreach($categories as $category) <option value="{{$category->id}}" @if($category->id==$row->parent_id) selected @endif>{{$category->name}}</option> @endforeach @endif
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                    <div class="col-sm-12">
                                        <input type="hidden" name="id" value="{{$row->id}}">
                                        <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-fullname">{{__('lang.admin_category_name')}} <span class="required">*</span></label>
                                        <input type="text" class="form-control dt-full-name" id="basic-icon-default-fullname" placeholder="{{__('lang.admin_category_name_placeholder')}}" name="name" value="{{$row->name}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_category_color')}} <span class="required">*</span></label>
                                        <input type="color" id="basic-icon-default-uname" class="form-control dt-uname" name="color" value="{{$row->color}}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_category_image')}} <span class="required">*</span></label>
                                        <div class="d-flex">
                                            <img src="{{ asset('uploads/category')}}/{{$row->image}}" id="image-preview_{{$row->id}}" class="rounded me-50" alt="profile image" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('upload/no-image.png') }}`" />
                                            <div class="mt-75 ms-1">
                                            <label class="btn btn-primary me-75 mb-0" for="change-picture_{{$row->id}}">
                                                <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                                <input class="form-control" type="file" id="change-picture_{{$row->id}}" hidden accept="image/png, image/jpeg, image/jpg" name="image" onclick="showImagePreview('change-picture_{{$row->id}}','image-preview_{{$row->id}}',512,512);"/>
                                                <span class="d-block d-sm-none">
                                                <i class="me-0" data-feather="edit"></i>
                                                </span>
                                            </label>
                                            <p>{{__('lang.admin_category_image_resolution')}}</p>
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