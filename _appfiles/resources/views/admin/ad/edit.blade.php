@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/tagify/tagify.css')}}" />

<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="add-record" onsubmit="return validateAd('edit-record');" action="{{url('admin/update-ad')}}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="id" value="{{$row->id}}">
    <!--  -->
    <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/ads')}}"> {{__('lang.admin_ads')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_edit_ad')}}</h4>
    <div class="float-right py-3">
        <button type="submit" class="btn btn-primary me-sm-3 me-1">{{__('lang.admin_button_save_changes')}}</button>
        <a href="{{url('admin/ads')}}" class="btn btn-label-secondary">{{__('lang.admin_button_back')}}</a>
    </div>
    <div class="row">
        <div class="col">
            <div class="card mb-3">                
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button
                            type="button"
                            class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#form-tabs-personal"
                            role="tab"
                            aria-selected="true"
                            >
                            {{__('lang.admin_basic_info')}}
                            </button>
                        </li>
                        <li class="nav-item">
                            <button
                            type="button"
                            class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#form-tabs-source"
                            role="tab"
                            aria-selected="false"
                            >
                            {{__('lang.admin_source_details')}}
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade active show" id="form-tabs-personal" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label" for="title">{{__('lang.admin_title')}} <span class="required">*</span></label>
                                <input type="text" id="title" class="form-control" name="title" placeholder="{{__('lang.admin_title_placeholder')}}" value="{{$row->title}}"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="start_date">{{__('lang.admin_start_date')}} <span class="required">*</span></label>
                                <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM AA" name="start_date" readonly="readonly" value="{{$row->start_date}}"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="end_date">{{__('lang.admin_end_date')}}<span class="required">*</span></label>
                                <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM AA" name="end_date" readonly="readonly" value="{{$row->end_date}}"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="frequency">{{__('lang.admin_frequency')}} <span class="required">*</span></label>
                                <input type="text" id="frequency" class="form-control" name="frequency" placeholder="{{__('lang.admin_frequency_placeholder')}}" value="{{$row->frequency}}" onkeypress="return (event.charCode !=8 && event.charCode ==0 || (event.charCode >= 48 && event.charCode <= 57))"/>
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top: 15px;">
                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_media_type')}} <span class="required">*</span></label>
                            <div class="col mt-2">
                                <div class="form-check form-check-inline">
                                    <input
                                    name="media_type"
                                    class="form-check-input"
                                    type="radio"
                                    value="image"
                                    id="image"
                                    @if($row->media_type=='image') checked="" @endif
                                    onclick="selectMediaType('image')"
                                    />
                                    <label class="form-check-label" for="image"
                                    >{{__('lang.admin_image')}}</label
                                    >
                                </div>
                                <div class="form-check form-check-inline">
                                    <input
                                    name="media_type"
                                    class="form-check-input"
                                    type="radio"
                                    value="video"
                                    id="video"
                                    onclick="selectMediaType('video')"
                                    @if($row->media_type=='video') checked="" @endif
                                    />
                                    <label class="form-check-label" for="video">
                                    {{__('lang.admin_video')}}
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input
                                    name="media_type"
                                    class="form-check-input"
                                    type="radio"
                                    value="video_url"
                                    id="video_url"
                                    onclick="selectMediaType('video_url')"
                                    @if($row->media_type=='image_url') checked="" @endif
                                    />
                                    <label class="form-check-label" for="video_url">
                                    {{__('lang.admin_video_url')}}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 showImage @if($row->media_type=='video' || $row->media_type=='video_url') hide @endif" style="margin-top: 15px;">
                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_image')}} <span class="required">*</span></label>
                            <input class="form-control" type="file" name="image" id="change-picture" accept="image/*" onclick="showImagePreview('change-picture','image-preview',1080,1920);">
                            <p class="mt-10">{{__('lang.admin_resolution_ad_image')}}</p>
                            <img src="{{url('uploads/ad/'.$row->media)}}" class="rounded me-50 @if($row->media=='') hide @endif" id="image-preview" alt="image" height="80" width="80" style="margin-top: 20px;"/>
                        </div>
                        <div class="col-md-12 @if($row->media_type=='image' || $row->media_type=='video_url') hide @endif showVideo" style="margin-top: 15px;">
                            <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_video')}} <span class="required">*</span></label>
                            <input class="form-control" type="file" name="media" id="change-video" accept="video/*" onclick="showVideoPreview('change-video', 'video-preview', 1080, 1920);">
                            <video id="video-preview" controls="false" style="width: 30%;margin-top: 20px;" class="video-container @if($row->media=='') hide @endif">
                                <source class="video-preview" src="{{url('uploads/ad/video/'.$row->media)}}" type="video/mp4" controls="false">
                                <source class="video-preview" src="{{url('uploads/ad/video/'.$row->media)}}" type="video/webm">Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="col-md-6 @if($row->media_type=='video' || $row->media_type=='image') hide @endif showVideoUrl" style="margin-top: 15px;">
                            <label class="form-label" for="video_url">{{__('lang.admin_video_url')}} <span class="required">*</span></label>
                            <input class="form-control" type="text" name="video_url" id="video_url" value="{{$row->video_url}}" placeholder="{{__('lang.admin_video_url_placeholder')}}">
                        </div>
                    </div>
                    <div class="tab-pane fade" id="form-tabs-source" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="source_name">{{__('lang.admin_source_name')}}</label>
                                <input type="text" id="source_name" class="form-control" name="source_name" placeholder="{{__('lang.admin_source_name_placeholder')}}" value="{{$row->source_name}}"/>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="source_link">{{__('lang.admin_source_link')}}</label>
                                <input id="source_link" class="form-control" name="source_link" placeholder="{{__('lang.admin_source_link_placeholder')}}" value="{{$row->source_link}}"/>
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
    .then(editor => {
        const prefilledValue = document.getElementById('seo_description').value;
        editor.setData(prefilledValue);
        // Set the prefilled value on keyup event
        editor.model.document.on('change', () => {
            const updatedValue = editor.getData();
            var stripedHtml = updatedValue.replace(/<[^>]+>/g, '');
            document.getElementById('seo_description').value = stripedHtml;
        });
    }).catch(error => {
        console.log(error);
    });
    $(".flatpickr-datetime-new").flatpickr({
      enableTime: true,
      dateFormat: 'Y-m-d H:i'
    });
</script>
@endsection