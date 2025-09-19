@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/tagify/tagify.css')}}" />

<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="add-record" onsubmit="return validateBlog('add-record');" action="{{url('admin/add-quote')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/blog')}}"> {{__('lang.admin_blog')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_add_quote')}}</h4>
        <div class="float-right py-3">
            <input type="submit" id="publish" name="button_name" class="btn btn-success me-sm-3 me-1" value="{{__('lang.admin_publish')}}"/>
            <input type="submit" id="submit" name="button_name" class="btn btn-primary me-sm-3 me-1" value="{{__('lang.admin_submit')}}"/>
            <input type="submit" id="draft" name="button_name" class="btn btn-warning me-sm-3 me-1" value="{{__('lang.admin_draft')}}"/>
            <a href="{{url('admin/blog')}}" class="btn btn-label-secondary">{{__('lang.admin_button_cancel')}}</a>
        </div>
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" id="add-record" action="{{url('admin/add-quote')}}" onsubmit="return validateQuotes('add-record');" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="{{Request::segment('4')}}">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="formtabs-first-name">{{__('lang.admin_category')}} <span class="required">*</span></label>
                                <select id="category_id" class="select2 form-select category_id" placeholder="Select Category" name="category_id[]" multiple onchange="showSubCategory('category_id','subCategory');">
                                    <option value="">{{__('lang.admin_select_category')}}</option>
                                    @foreach($categories as $category)
                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="formtabs-first-name">{{__('lang.admin_subcategory')}}</label>
                                <select id="sub_category_id" class="select2 form-select sub_category_id subCategory" name="sub_category_id[]" multiple>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="title">{{__('lang.admin_title')}} <span class="required">*</span></label>
                                <input type="text" id="title" class="form-control" name="title" placeholder="{{__('lang.admin_title_placeholder')}}" />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label" for="description">{{__('lang.admin_description')}}</label>
                                <textarea class="form-control" name="description" id="editor" placeholder="{{__('lang.admin_description_placeholder')}}" ></textarea>
                            </div>
                            <div class="col-md-6 select2-primary">
                                <label class="form-label" for="author_name">{{__('lang.admin_author')}}</label>
                                <input type="text" id="author_name" class="form-control" name="author_name" placeholder="{{__('lang.admin_author_placeholder')}}" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="schedule_date">{{__('lang.admin_schedule_date')}}</label>
                                <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM AA" name="schedule_date" readonly="readonly"/>
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="mb-1">
                                <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_background_image')}} <span class="required">*</span></label>
                                <div class="d-flex">
                                    <img src="" class="rounded me-50 hide" id="image-preview" alt="background image" height="80" width="80"/>
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
                        <!-- <div class="row">
                            <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                <a href="{!! url('admin/cms') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                            </div>
                        </div> -->
                    </form>
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