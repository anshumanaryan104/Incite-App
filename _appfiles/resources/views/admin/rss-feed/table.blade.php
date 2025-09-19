<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_category')}}</th>
            <th>{{__('lang.admin_language')}}</th>
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
                    <td>@if(isset($row->category) && $row->category!='') @if(isset($row->category->name) && $row->category->name!='') {{$row->category->name}} @else -- @endif @else -- @endif</td>
                    <td>@if(isset($row->language) && $row->language!='') @if(isset($row->language->name) && $row->language->name!='') {{$row->language->name}} @else -- @endif @else -- @endif</td>
                    <td>
                        @if (Gate::check('update-social-media'))
                            @if(isset($row->rss_name) && $row->rss_name!='')<a class="cursor-pointer" data-bs-toggle="offcanvas" data-bs-target="#edit-new-record_{{$row->id}}" aria-controls="edit-new-record_{{$row->id}}">{{$row->rss_name}}</a>@else -- @endif
                        @else
                            @if($row->rss_name!=''){{$row->rss_name}}@else -- @endif
                        @endif     
                    </td>
                    <td>@if(isset($row->rss_url) && $row->rss_url!=''){{$row->rss_url}}@else -- @endif</td>
                    @can('update-social-media-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-rss-feeds-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-rss-feeds-status/'.$row->id.'/1')}}">
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
                                <a class="dropdown-item" href="{{ url('admin/check-rss/'.$row->id) }}">
                                <i class="ti ti-check me-1 margin-top-negative-4"></i> {{__('lang.admin_check')}} </a>
                                @can('delete-social-media')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-rss-feeds', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                        <div class="offcanvas offcanvas-end" id="edit-new-record_{{$row->id}}">
                            <div class="offcanvas-header border-bottom">
                                <h5 class="offcanvas-title" id="exampleModalLabel">{{__('lang.admin_edit_rss_feeds')}}</h5>
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body flex-grow-1">
                                <form class="add-new-record pt-0 row g-2" id="edit-record_{{$row->id}}" action="{{url('admin/update-social-media')}}" method="POST" enctype="multipart/form-data" onsubmit="return validateRss('edit-record_{{$row->id}}');"> @csrf 
                                    <input type="hidden" name="id" value="{{$row->id}}">
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="name">{{__('lang.admin_category')}} <span class="required">*</span></label>
                                            <select class="form-control" name="category_id">
                                                <option value="">{{__('lang.admin_select_category')}}</option> 
                                                @if(isset($categories) && count($categories))
                                                    @foreach($categories as $category)
                                                        <optgroup label="{{$category->name}}">
                                                        <option value="{{$category->id}}" @if($row->category_id==$category->id) selected @endif>{{$category->name}}</option>
                                                        @if(isset($category->sub_category) && count($category->sub_category))
                                                            @foreach($category->sub_category as $sub_category)
                                                            <option value="{{$sub_category->id}}" @if($row->category_id==$sub_category->id) selected @endif>{{$sub_category->name}}</option>
                                                            @endforeach
                                                        @endif
                                                        </optgroup>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="language_id">{{__('lang.admin_language')}} <span class="required">*</span></label>
                                            <select class="form-control" name="language_id">
                                                <option value="">{{__('lang.admin_select_language')}}</option> 
                                                @if(isset($languages) && count($languages))
                                                    @foreach($languages as $language)
                                                        <option value="{{$language->id}}" @if($row->language_id==$language->id) selected @endif>{{$language->name}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="rss_name" placeholder="{{__('lang.admin_name_placeholder')}}" name="rss_name" value="{{$row->rss_name}}">
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="mb-1">
                                            <label class="form-label" for="url">{{__('lang.admin_url')}} <span class="required">*</span></label>
                                            <input type="text" class="form-control dt-full-name" id="rss_url" placeholder="{{__('lang.admin_url_placeholder')}}" name="rss_url" value="{{$row->rss_url}}">
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