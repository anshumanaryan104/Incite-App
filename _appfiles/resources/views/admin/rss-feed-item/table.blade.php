<table class="table">
    <thead class="table-light">
        <tr>
            <th>{{__('lang.admin_image')}}</th>
            <th>{{__('lang.admin_title')}}</th>
            @can('store-post')
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
                    <td>
                        @if(isset($row['image']) && $row['image']!=null && $row['image']!='')
                            <img onerror="this.onerror=null;this.src='<?php echo url("uploads/no-image.png") ?>';"  src="{{$row['image']}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("uploads/no-image.png") ?>';">
                        @else
                            <img onerror="this.onerror=null;this.src='<?php echo url("uploads/no-image.png") ?>';"  src="{{url('upload/author/default.png')}}" width="150" onerror="this.onerror=null;this.src='<?php echo url("uploads/no-image.png") ?>';">
                        @endif
                    </td>
                    <td>
                        <?php echo substr($row['title'], 0,90);?></br>
                        <span><small><?php echo substr($row['description'], 0,150)."...";?></small></span>
                    </td>
                    @can('store-post')
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="ti ti-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu">
                                <form action="{{ url('admin/store-rss-item') }}" method="POST"> @csrf @method('POST')
                                    <input type="hidden" name="category_id" value="{{$row['category_id']}}">
                                    <input type="hidden" name="title" value="{{$row['title']}}">
                                    <input type="hidden" name="description" value="{{$row['description']}}">
                                    <input type="hidden" name="url" value="{{$row['link']}}">
                                    <input type="hidden" name="source_name" value="{{$row['source_name']}}">
                                    <input type="hidden" name="image" value="{{$row['image']}}">
                                    <input type="hidden" name="pubDate" value="{{$row['pubDate']}}">
                                    <button type="submit" class="dropdown-item" data-toggle="tooltip" data-placement="bottom" title="{{__('lang.admin_save_post')}}">
                                    <i class="ti ti-device-floppy me-1 margin-top-negative-4"></i>{{__('lang.admin_save_post')}}</button>
                                </form>
                            </div>
                        </div>
                    </td>
                    @endcan
                </tr> 
            @endforeach 
        @else 
            <tr>
                <td colspan="3" class="record-not-found">
                    <span>{{__('lang.admin_record_not_found')}}</span>
                </td>
            </tr> 
        @endif 
    </tbody>
</table>