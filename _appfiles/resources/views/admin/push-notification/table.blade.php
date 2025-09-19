<table class="table">
    <thead class="table-light">
        <tr>
            <th>{{__('lang.admin_user_names')}}</th>
            <th>{{__('lang.admin_notification_type')}}</th>
            <th>{{__('lang.admin_title')}}</th>
            <th>{{__('lang.admin_description')}}</th>
            <th>{{__('lang.admin_sending_date_time')}}</th>
            <th>{{__('lang.admin_action')}}</th>
        </tr>
    </thead>
    <tbody>    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr>
                    <td>
                        @if($row->user_id=='')
                            {{__('lang.admin_guest')}}
                        @else
                            @if($row->send_to=='all_user_with_guest')
                                {{__('lang.admin_all_user_with_guest')}}
                            @elseif($row->send_to=='all_user_without_guest')
                                {{__('lang.admin_all_user_without_guest')}}
                            @else
                                {{$row->user_names}}
                            @endif
                        @endif
                    </td>
                    <td>
                        @if($row->send_to=='all_user_with_guest')
                            {{__('lang.admin_all_user_with_guest')}}
                        @elseif($row->send_to=='all_user_without_guest')
                            {{__('lang.admin_all_user_without_guest')}}
                        @elseif($row->send_to=='only_guest')
                            {{__('lang.admin_only_guest')}}
                        @elseif($row->send_to=='specific_user')
                            {{__('lang.admin_specific_user')}}
                        @endif
                    </td>
                    <td>
                        {{$row->title}}                        
                    </td>
                    <td>
                        <?php echo $row->description; ?>
                    </td>
                    <td>
                        {{date("d-m-Y",strtotime($row->created_at))}}</br>
                        <span>{{date("h:i A",strtotime($row->created_at))}}</span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" title="{{__('lang.admin_select_action')}}">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <form id="deleteForm_{{$row->id}}" action="{{ url('admin/delete-push-notification', $row->id) }}" method="POST" onsubmit="return deleteConfirm('deleteForm_{{$row->id}}');"> @csrf @method('DELETE') <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_delete')}}">
                                    <i class="ti ti-trash me-1 margin-top-negative-4"></i>{{__('lang.admin_delete')}} </button>
                                </form>
                            </div>
                        </div>
                    </td>
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