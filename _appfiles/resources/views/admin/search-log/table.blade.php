<table class="table">
    <thead class="table-light">
        <tr class="text-nowrap">
            <th>{{__('lang.admin_id')}}</th>
            <!-- <th>{{__('lang.admin_search_by')}}</th> -->
            <th>{{__('lang.admin_search_log')}}</th>
            <th>{{__('lang.admin_search_count')}}</th>
            <th>{{__('lang.admin_searched_date')}}</th>
        </tr>
    </thead>
    <tbody>    
        @php $i=0; @endphp 
        @if(count($result) > 0) 
            @foreach($result as $row) 
                @php $i++; @endphp 
                <tr>
                    <td>{{$i}}</td>
                    <!-- <td>@if(isset($row->user->name) && $row->user->name!=''){{$row->user->name}}@else--@endif</td> -->
                    <td>@if(isset($row->keyword) && $row->keyword!=''){{$row->keyword}}@else--@endif</td>
                    <td>{{$row->count}}</td>
                    <td>{{date(setting('date_format'),strtotime($row->created_at))}}</td>
                </tr> 
            @endforeach 
        @else 
            <tr>
                <td colspan="5" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>