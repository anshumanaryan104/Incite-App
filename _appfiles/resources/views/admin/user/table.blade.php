<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_name')}}</th>
            <th>{{__('lang.admin_phone')}}</th>
            <th>{{__('lang.admin_email')}}</th>
            <th>{{__('lang.admin_login_from')}}</th>
            <th>{{__('lang.admin_joined_date')}}</th>
            @can('update-user-status')
            <th>{{__('lang.admin_status')}}</th>
            @endcan
            @canany(['personlization', 'delete-user'])  
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
                    <td>{{$row->name}}</td>
                    <td>@if($row->phone!=''){{$row->phone}}@else--@endif</td>
                    <td>{{$row->email}}</td>
                    <td>{{ucfirst($row->login_from)}}</td>
                    <td>{{$row->created_at}}</td>
                    @can('update-user-status')
                    <td> @if($row->status==1) <a href="{{url('admin/update-user-status/'.$row->id.'/0')}}">
                        <span class="badge bg-success">{{__('lang.admin_active')}}</span>
                        </a> @else <a href="{{url('admin/update-user-status/'.$row->id.'/1')}}">
                        <span class="badge bg-danger">{{__('lang.admin_inactive')}}</span>
                        </a> @endif 
                    </td>
                    @endcan
                    @canany(['personlization', 'delete-user'])                    
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                @can('personlization')
                                <a class="dropdown-item" href="{{url('/admin/personlization/'.$row->id)}}">
                                <i class="ti ti-id   me-1 margin-top-negative-4"></i> {{__('lang.admin_personlization')}} </a>
                                @endcan
                                @can('delete-user')
                                <form id="deleteForm_{{$row->id}}" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');" action="{{ url('admin/delete-user', $row->id) }}" method="POST"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
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
                <td colspan="9" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>