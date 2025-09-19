@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/tagify/tagify.css')}}" />

<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="add-record" onsubmit="return validateBlog('add-record');" action="{{url('admin/add-post')}}" method="POST" enctype="multipart/form-data">
        @csrf
        <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/blog')}}"> {{__('lang.admin_post')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_add_post')}}</h4>
        <div class="float-right py-3">
            <!-- <input type="submit" id="publish" name="button_name" class="btn btn-success me-sm-3 me-1" value="{{__('lang.admin_publish')}}"/> -->
            <input type="submit" id="submit" name="button_name" class="btn btn-primary me-sm-3 me-1" value="{{__('lang.admin_submit')}}"/>
            <!-- <input type="submit" id="draft" name="button_name" class="btn btn-warning me-sm-3 me-1" value="{{__('lang.admin_draft')}}"/> -->
            <a href="{{url('admin/blog')}}" class="btn btn-label-secondary">{{__('lang.admin_button_cancel')}}</a>
        </div>
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" id="add-record" action="{{url('admin/add-post')}}" onsubmit="return validateQuotes('add-record');" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="type" value="post_feed">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="description">{{__('lang.admin_description')}}</label>
                                <textarea class="form-control" name="description" id="editor" placeholder="{{__('lang.admin_description_placeholder')}}" ></textarea>
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top: 15px;">
                            <label class="form-label width-100-percent" for="basic-icon-default-uname">{{__('lang.admin_image')}} <span class="required">*</span></label>
                            <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#basicModal" onclick="hideImages();">
                                {{__('lang.admin_upload_image')}}
                            </button>
                            <input type="hidden" name="image[]" id="image_names"/>
                            <p>{{__('lang.admin_resolution_background_image')}}</p>
                            <!-- <input class="form-control" type="file" name="image[]" id="formFileMultiple" multiple="" accept="image/*" onclick="showMultipleImagePreview('formFileMultiple','image-preview',1000,1000);">-->
                            <div class="col hide showLoader">
                                <!-- Pluse -->
                                <div class="sk-wave sk-primary">
                                    <div class="sk-wave-rect"></div>
                                    <div class="sk-wave-rect"></div>
                                    <div class="sk-wave-rect"></div>
                                    <div class="sk-wave-rect"></div>
                                    <div class="sk-wave-rect"></div>
                                </div>
                            </div>
                            <div class="row row-cols-1 row-cols-md-5 g-4 mb-5 image_position hide" id="previewsContainer" style="margin-top: 15px;"> 
                                @include('admin/blog/partials/image_preview')
                            </div>
                        </div>    
                    </form>
                </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal fade" id="basicModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">{{__('lang.admin_blog_images')}}</h5>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <form action="{{url('/admin/store-image')}}" method="post" class="dropzone needsclick" id="dropzone-multi">
                        @csrf
                        <div class="dz-message needsclick">
                            Drop files here or click to upload
                        </div>
                        <div class="fallback">
                            <input name="file" type="file" accept="image/*"/>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal" onclick="showImages();">
                    Close
                </button>
            </div>
        </div>
    </div>
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