<table class="table">
    <thead class="table-light">
        <tr>
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_media')}}</th>
            <th>{{__('lang.admin_title')}}</th>
            <th>{{__('lang.admin_timestamp')}}</th>
            <th>{{__('lang.admin_frequency')}}</th>
            <th>{{__('lang.admin_created_date_time')}}</th>
            @can('update-ad-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-ad', 'delete-ad'])
            <th>{{__('lang.admin_action')}}</th>
            @endcanany
        </tr>
    </thead>
    <tbody id="ad_table">    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr class="row1" data-id="{{ $row->id }}">
                    <td>{{$i}}</td>
                    <td>
                        @if($row->media_type=='image')
                            <img src="{{ url('uploads/ad/'.$row->media)}}" class="me-75" height="80" width="80" alt="{{$row->title}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                        @elseif($row->media_type=='video_url')
                            <iframe width="100%" height="160" src="https://www.youtube.com/embed/{{\Helpers::getVideoIdFromYoutubeUrl($row->video_url)}}" frameborder="0" allowfullscreen></iframe>
                        @elseif($row->media_type=='video')
                            <video controls="false" style="width: 40%;">
                                <source class="video-preview" src="{{url('uploads/ad/video/'.$row->media)}}" type="video/mp4" controls="false">
                                <source class="video-preview" src="{{url('uploads/ad/video/'.$row->media)}}" type="video/webm">Your browser does not support the video tag.
                            </video>
                        @endif
                    </td>
                    <td>
                        @if (Gate::check('update-ad'))
                            <a href="{{url('/admin/update-ad/'.$row->id)}}">{{$row->title}}</a>
                        @else
                            {{$row->title}}
                        @endif
                    </td>
                    <td>
                        {{date("d-m-Y",strtotime($row->start_date))}} - {{date("d-m-Y",strtotime($row->end_date))}}
                    </td>
                    <td>
                        {{$row->frequency}}
                    </td>
                    <td>
                        {{date("d-m-Y",strtotime($row->created_at))}}</br>
                        <span>{{date("h:i A",strtotime($row->created_at))}}</span>
                    </td>
                    @can('update-ad-status')
                    <td>
                        @if($row->status==1) 
                        <a href="{{url('admin/update-ad-status/'.$row->id.'/0')}}">
                            <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-ad-status/'.$row->id.'/1')}}">
                            <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> 
                        @endif 
                    </td>
                    @endcan
                    @canany(['update-ad', 'delete-ad'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-ad')
                                <a class="dropdown-item" href="{{url('/admin/update-ad/'.$row->id)}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('delete-ad')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-ad', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="Delete">
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
                <td colspan="8" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>