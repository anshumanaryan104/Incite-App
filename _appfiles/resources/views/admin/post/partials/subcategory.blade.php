@if(count($subcategory))
    @foreach($subcategory as $subcategory_data)
        <option value="{{$subcategory_data->id}}" @if(isset($sub_cat_id) && count($sub_cat_id)) @if(in_array($subcategory_data->id,$sub_cat_id)) selected @endif @endif>{{$subcategory_data->name}}</option>
    @endforeach
@endif