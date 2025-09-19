@if(isset($images) && count($images))
    @foreach($images as $image)
        <div class="col row1" data-id="{{ $image->id }}">
            <div class="card h-100">
                <img class="card-img-top" src="{{ url('uploads/blog/768x428/'.$image->image)}}" alt="{{$image->image}}" />
                <div class="card-body" style="text-align: center;padding: 0;">
                    <button class="btn btn-label-danger mt-4 mb-4 waves-effect" type="button" onclick="deleteImage('{{ $image->id }}');">
                        <i class="ti ti-x ti-xs me-1"></i>
                        <span class="align-middle">{{__('lang.admin_delete')}}</span>
                    </button>
                </div> 
            </div>
        </div>
    @endforeach
@endif