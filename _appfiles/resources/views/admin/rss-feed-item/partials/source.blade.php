@if(count($source))
    @foreach($source as $source_data)
        <option value="{{$source_data->id}}" @if(isset($GET['source_id']) && $GET['source_id']!='') @if($GET['source_id']==$source_data->id) selected @endif @endif>{{$source_data->rss_name}}</option>
    @endforeach
@endif