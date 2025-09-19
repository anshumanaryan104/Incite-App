@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/tagify/tagify.css')}}" />

<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="edit-record" onsubmit="return validateQuotes('edit-record');" action="{{url('admin/update-quote')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="id" value="{{$row->id}}">
        <!--  -->
        <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/blog')}}"> {{__('lang.admin_blog')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_edit_quote')}}</h4>
        <div class="float-right py-3">
            @if($row->status==2)
                <input type="submit" id="publish" name="button_name" class="btn btn-success me-sm-3 me-1" value="{{__('lang.admin_publish')}}"/>
                <input type="submit" id="submit" name="button_name" class="btn btn-primary me-sm-3 me-1" value="{{__('lang.admin_submit')}}"/>
            @elseif($row->status==3 || $row->status==0)
                <input type="submit" id="publish" name="button_name" class="btn btn-success me-sm-3 me-1" value="{{__('lang.admin_publish')}}"/>         
            @endif
            <input type="submit" id="update" name="button_name" class="btn btn-primary me-sm-3 me-1" value="{{__('lang.admin_update')}}"/>
            <a href="{{url('admin/blog')}}" class="btn btn-label-secondary">{{__('lang.admin_button_cancel')}}</a>
        </div>
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="formtabs-first-name">{{__('lang.admin_category')}} <span class="required">*</span></label>
                                <select id="category_id" class="select2 form-select category_id" name="category_id[]" multiple onchange="showSubCategory('category_id','subCategory');">
                                    <option value="">{{__('lang.admin_select_category')}}</option>
                                    @foreach($categories as $category)
                                        <option @if(isset($row->categoryArr) && count($row->categoryArr)) @if(in_array($category->id,$row->categoryArr)) selected @endif @endif value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="formtabs-first-name">{{__('lang.admin_subcategory')}}</label>
                                <select id="sub_category_id" class="select2 form-select sub_category_id subCategory" name="sub_category_id[]" multiple>
                                    @if(isset($subcategory) && count($subcategory))
                                        @foreach($subcategory as $subcategory_data)
                                            <option value="{{$subcategory_data->id}}" @if(isset($row->subcategoryArr) && count($row->subcategoryArr)) @if(in_array($subcategory_data->id,$row->subcategoryArr)) selected @endif @endif>{{$subcategory_data->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="title">{{__('lang.admin_title')}} <span class="required">*</span></label>
                                <input type="text" id="title" class="form-control" name="title" placeholder="{{__('lang.admin_title_placeholder')}}" value="{{$row->title}}" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="description">{{__('lang.admin_description')}}</label>
                                <textarea class="form-control" name="description" id="editor" placeholder="{{__('lang.admin_description_placeholder')}}" value="{{$row->description}}">{{$row->description}}</textarea>
                            </div>
                            <div class="col-md-6 select2-primary">
                                <label class="form-label" for="author_name">{{__('lang.admin_author')}}</label>
                                <input type="text" id="author_name" class="form-control" name="author_name" placeholder="{{__('lang.admin_author_placeholder')}}" value="{{$row->author_name}}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="schedule_date">{{__('lang.admin_schedule_date')}}</label>
                                <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM AA" name="schedule_date" readonly="readonly" value="{{$row->schedule_date}}"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="created_at">{{__('lang.admin_created_date')}}</label>
                                <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM AA" name="created_at" readonly="readonly" value="{{$row->created_at}}"/>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-1">
                                <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_background_image')}} <span class="required">*</span></label>
                                <div class="d-flex">
                                    <img src="{{ asset('uploads/blog')}}/{{$row->background_image}}" id="image-preview_{{$row->id}}" class="rounded me-50" alt="profile image" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                                    <input type="hidden" id="image_name" value="{{$row->background_image}}">
                                    <div class="mt-75 ms-1">
                                        <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                        <span class="d-none d-sm-block">{{__('lang.admin_upload_background_image')}}</span>
                                        <input class="form-control" type="file" name="background_image" id="change-picture" hidden accept="image/*" name="background_image" onclick="showImagePreview('change-picture','image-preview',1080,960);"/>
                                        <span class="d-block d-sm-none">
                                            <i class="me-0" data-feather="edit"></i>
                                        </span>
                                        </label>
                                        <p>{{__('lang.admin_resolution_background_image')}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<script type="text/javascript">
    ClassicEditor
    .create(document.querySelector('#editor'), {
    })
    .catch(error => {
        console.log(error);
    });
    $(".flatpickr-datetime-new").flatpickr({
      enableTime: true,
      dateFormat: 'Y-m-d H:i'
    });
</script>
@endsection