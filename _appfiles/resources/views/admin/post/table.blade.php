<table class="table">
    <thead class="table-light">
        <tr>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_description')}}</th>
            <th>{{__('lang.admin_views')}}</th>
            <th>@sortablelink('created_at', __('lang.admin_created_date_time'))</th>
            @can('update-blog-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-blog', 'delete-blog','send-notification','analytics','blog-translation'])
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
                    <td>
                        @if($row->type=="post")
                        @if($row->image!='')
                        <img src="{{ url('uploads/blog/80x45/'.$row->image->image)}}" class="me-75" height="45" width="80" alt="{{$row->title}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                        @else
                        <img src="{{ url('uploads/no-image.png')}}" class="me-75" height="45" width="80" alt="{{$row->title}}"/>
                        @endif
                        @else
                        <img src="{{ url('uploads/blog/'.$row->background_image)}}" class="me-75"  width="80" alt="{{$row->title}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                        @endif

                    </td>
                    <td>
                        <?php echo $row->description; ?>
                    </td>
                    <td>
                        {{$row->view_count}}
                    </td>
                    <td>
                        {{date("d-m-Y",strtotime($row->created_at))}}</br>
                        <span>{{date("h:i A",strtotime($row->created_at))}}</span>
                    </td>
                    @can('update-blog-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-blog-status/'.$row->id.'/0')}}" title="{{__('lang.admin_active')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-blog-status/'.$row->id.'/1')}}" title="{{__('lang.admin_inactive')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    <!-- <td>
                        @if($row->status==1)
                            <a href="javascript:;" class="btn btn-xs btn-success waves-effect waves-light" title="{{__('lang.admin_publish')}}">{{__('lang.admin_publish')}}</a>
                        @elseif($row->status==2)
                            <a href="javascript:;" class="btn btn-xs btn-warning waves-effect waves-light" title="{{__('lang.admin_draft')}}">{{__('lang.admin_draft')}}</a>
                        @elseif($row->status==3)
                            <a href="javascript:;" class="btn btn-xs btn-primary waves-effect waves-light" title="{{__('lang.admin_submit')}}">{{__('lang.admin_submit')}}</a>
                        @elseif($row->status==4)
                            <a href="javascript:;" class="btn btn-xs btn-info waves-effect waves-light" title="{{__('lang.admin_scheduled')}}">{{__('lang.admin_scheduled')}}</a>
                        @elseif($row->status==0)
                            <a href="javascript:;" class="btn btn-xs btn-danger waves-effect waves-light" title="{{__('lang.admin_unpublish')}}">{{__('lang.admin_unpublish')}}</a>
                        @endif
                    </td> -->
                    @canany(['update-blog', 'delete-blog','send-notification','analytics','blog-translation'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" title="{{__('lang.admin_select_action')}}">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-blog')
                                <a class="dropdown-item" href="{{url('/admin/update-post/'.$row->id)}}" title="{{__('lang.admin_edit')}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('analytics')
                                <a class="dropdown-item" href="{{url('/admin/post-analytics/'.$row->id)}}" title="{{__('lang.admin_analytics')}}">
                                <i class="ti ti-report-analytics me-1 margin-top-negative-4"></i> {{__('lang.admin_analytics')}} </a>
                                @endcan
                                @can('delete-blog')
                                <form id="deleteForm_{{$row->id}}" action="{{ url('admin/delete-blog', $row->id) }}" method="POST" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                                @endcan
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