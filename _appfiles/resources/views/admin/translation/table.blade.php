<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <th>{{__('lang.admin_group')}}</th>
            <th>{{__('lang.admin_keyword')}}</th>
            <th>{{__('lang.admin_value')}}</th>
            <!-- <th>{{__('lang.admin_language')}}</th> -->
            @can('update-translation')
            <th>{{__('lang.admin_action')}}</th>
            @endcan
        </tr>
    </thead>
    <tbody>    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr>
                    <td>{{$i}}</td>
                    <td>@if($row->group!=''){{$row->group}}@else -- @endif</td>
                    <td>
                        @if (Gate::check('update-translation'))
                            @if($row->keyword!='')<a href="{{url('/admin/update-translation/'.$row->id)}}">{{$row->keyword}}</a>@else -- @endif
                        @else
                            @if($row->keyword!=''){{$row->keyword}}@else -- @endif
                        @endif
                    </td>
                    <td>@if($row->value!=''){{$row->value}}@else -- @endif</td>
                    <!-- <td>@if($row->language_id!=''){{$row->language_id}}@else -- @endif</td> -->
                    @can('update-translation')
                    <td>
                        <div class="dropdown">
                            <button type="buttson" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{url('/admin/update-translation/'.$row->id)}}">
                                <i class="ti ti-pencil me-1 margin-top-negative-4"></i> {{__('lang.admin_edit')}} </a>
                            </div>
                        </div>
                    </td>
                    @endcan
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