<table class="table">
    <thead class="table-light">
        <tr>
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_title')}}</th>
            <th class="width-45-percent">{{__('lang.admin_description')}}</th>
            @can('update-cms-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['update-cms', 'delete-cms', 'translation-cms'])
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
                        <img src="{{ url('uploads/cms/'.$row->image)}}" class="me-75" height="50" width="50" alt="{{$row->title}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                    </td>
                    <td>
                        @if (Gate::check('update-e-paper'))
                            <a href="{{url('/admin/update-cms/'.$row->id)}}">{{$row->title}}</a>
                        @else
                            @if($row->title!=''){{$row->title}}@else -- @endif
                        @endif 
                    </td>
                    <td><?php echo \Helpers::getLimiteCMSdDescriptionAdmin($row->description); ?></td>
                    @can('update-cms-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-cms-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-cms-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['update-cms', 'delete-cms', 'translation-cms'])
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('update-cms')
                                <a class="dropdown-item" href="{{url('/admin/update-cms/'.$row->id)}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                                @endcan
                                @can('translation-cms')
                                <a class="dropdown-item" href="{{url('/admin/translation-cms/'.$row->id)}}">
                                <i class="ti ti-language me-1 margin-top-negative-4"></i> {{__('lang.admin_translation')}} </a>
                                @endcan
                                @can('delete-cms')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-cms', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="Delete">
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
                <td colspan="6" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>