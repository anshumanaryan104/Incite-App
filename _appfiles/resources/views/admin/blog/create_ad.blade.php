@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/tagify/tagify.css')}}" />

<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/flatpickr/flatpickr.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/bootstrap-datepicker/bootstrap-datepicker.css')}}" />
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />

<div class="container-xxl flex-grow-1 container-p-y">
    <form id="add-record" onsubmit="return validateBlog('add-record');" action="{{url('admin/add-blog')}}" method="POST" enctype="multipart/form-data">
    @csrf
    <!--  -->
    <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/blog')}}"> Blog {{__('lang.admin_list')}} </a>/</span> Add Ad</h4>
    <div class="float-right py-3">
        <input type="submit" id="submit" name="button_name" class="btn btn-primary me-sm-3 me-1" value="Submit"/>
        <input type="submit" id="draft" name="button_name" class="btn btn-primary me-sm-3 me-1" value="Draft"/>
        <a href="{{url('admin/blog')}}" class="btn btn-label-secondary">Cancel</a>
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
                        Blog Basic Info
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-account"
                        role="tab"
                        aria-selected="false"
                        >
                        Seo Details
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
                        Source Details
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-social"
                        role="tab"
                        aria-selected="false"
                        >
                        Visibility
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                        type="button"
                        class="nav-link"
                        data-bs-toggle="tab"
                        data-bs-target="#form-tabs-voting"
                        role="tab"
                        aria-selected="false"
                        >
                        Voting/Pool Question
                        </button>
                    </li>
                    </ul>
                </div>

                <div class="tab-content">
                    <div class="tab-pane fade active show" id="form-tabs-personal" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="formtabs-first-name">Category <span class="required">*</span></label>
                                    <select id="category_id" class="select2 form-select category_id" name="category_id[]" multiple>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- <div class="col-md-6">
                                    <label class="form-label" for="formtabs-first-name">SubCategory</label>
                                    <select id="sub_category_id" class="select2 form-select" name="sub_category_id[]" multiple>
                                        @foreach($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                    </select>
                                </div> -->
                                <div class="col-md-12">
                                    <label class="form-label" for="title">Title <span class="required">*</span></label>
                                    <input type="text" id="title" class="form-control" name="title" placeholder="Enter title" />
                                </div>
                               <div class="col-md-12">
                                    <label class="form-label" for="description">Description</label>
                                    <textarea class="form-control" name="description" id="editor" placeholder="Enter description" ></textarea>
                                </div>
                                <div class="col-md-6 select2-primary">
                                    <label class="form-label" for="video_url">Youtube URL</label>
                                    <input type="text" id="video_url" class="form-control" name="video_url" placeholder="Enter video url" />
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="schedule_date">Schedule Date</label>
                                    <input type="text" class="form-control flatpickr-input active flatpickr-datetime" placeholder="YYYY-MM-DD HH:MM" name="schedule_date" readonly="readonly"/>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="mb-1">
                                    <label class="form-label" for="basic-icon-default-uname">Image <span class="required">*</span></label>
                                    <div class="d-flex">
                                        <!-- <img src="" class="rounded me-50 hide" id="image-preview" alt="image" height="80" width="80"/> -->
                                        <div class="mt-75 ms-1">
                                            <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                            <span class="d-none d-sm-block">Upload</span>
                                            <input class="form-control" type="file" name="image[]" id="change-picture" hidden accept="image/png, image/jpeg, image/jpg" name="image" onclick="showMultipleImagePreview('change-picture','image-preview',1000,1000);" multiple/>
                                            <span class="d-block d-sm-none">
                                                <i class="me-0" data-feather="edit"></i>
                                            </span>
                                            </label>
                                            <p>Resolution 512x512.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row row-cols-1 row-cols-md-5 g-4 mb-5" id="previewsContainer">
                                    <!-- <div class="col">
                                        <div class="card h-100">
                                            <img class="card-img-top" src="{{ asset('admin-assets/img/elements/2.jpg')}}" alt="Card image cap" />
                                            <div class="card-body" style="text-align: center;padding: 0;">
                                                <button class="btn btn-label-danger mt-4 waves-effect">
                                                    <i class="ti ti-x ti-xs me-1"></i>
                                                    <span class="align-middle">Delete</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../assets/img/elements/10.jpg" alt="Card image cap" />
                                        <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">
                                            This is a longer card with supporting text below as a natural lead-in to additional content.
                                            This content is a little bit longer.
                                        </p>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../assets/img/elements/4.jpg" alt="Card image cap" />
                                        <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">
                                            This is a longer card with supporting text below as a natural lead-in to additional content.
                                        </p>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../assets/img/elements/13.jpg" alt="Card image cap" />
                                        <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">
                                            This is a longer card with supporting text below as a natural lead-in to additional content.
                                            This content is a little bit longer.
                                        </p>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../assets/img/elements/14.jpg" alt="Card image cap" />
                                        <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">
                                            This is a longer card with supporting text below as a natural lead-in to additional content.
                                            This content is a little bit longer.
                                        </p>
                                        </div>
                                    </div>
                                    </div>
                                    <div class="col">
                                    <div class="card h-100">
                                        <img class="card-img-top" src="../../assets/img/elements/15.jpg" alt="Card image cap" />
                                        <div class="card-body">
                                        <h5 class="card-title">Card title</h5>
                                        <p class="card-text">
                                            This is a longer card with supporting text below as a natural lead-in to additional content.
                                            This content is a little bit longer.
                                        </p>
                                        </div>
                                    </div>
                                    </div> -->
                                </div>
                            </div>
                            <!-- <div class="pt-4">
                                <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                                <button type="reset" class="btn btn-label-secondary">Cancel</button>
                            </div> -->
                        
                    </div>
                    <div class="tab-pane fade" id="form-tabs-account" role="tabpanel">
                    
                        <div class="row g-3">
                        <!-- <div class="col-md-6">
                            <label class="form-label" for="slug">Slug <span class="required">*</span></label>
                            <input type="text" id="slug" class="form-control" name="slug" placeholder="Enter slug" />
                        </div> -->
                        <div class="col-md-6">
                            <label class="form-label" for="tags">Tags</label>
                            <input id="TagifyBasic" class="form-control" name="tags" value="Tag1, Tag2, Tag3" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="seo_title">Title (Meta Tag)</label>
                            <input type="text" id="seo_title" class="form-control" name="seo_title" placeholder="Enter Title" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="seo_keyword">Keywords (Meta Tag)</label>
                            <input type="text" id="seo_keyword" class="form-control" name="seo_keyword" placeholder="Enter Keywords" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="seo_tag">Tags (Meta Tag)</label>
                            <input id="TagifyBasic" class="form-control" name="seo_tag" value="Tag1, Tag2, Tag3" />
                        </div>
                        <div class="col-md-12">
                            <div class="form-password-toggle">
                            <label class="form-label" for="seo_description">Description</label>
                            <textarea id="seo_description" class="form-control" name="seo_description"  ></textarea>
                            </div>
                        </div>
                        </div>
                        <!-- <div class="pt-4">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                        </div> -->
                    
                    </div>
                    <div class="tab-pane fade" id="form-tabs-source" role="tabpanel">
                    
                        <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="source_name">Source Name</label>
                            <input type="text" id="source_name" class="form-control" name="source_name" placeholder="Enter Source Name" />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="source_link">Source Link</label>
                            <input id="source_link" class="form-control" name="source_link" placeholder="Enter Source Link" />
                        </div>
                        </div>
                        <!-- <div class="pt-4">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                        </div> -->
                    
                    </div>
                    <div class="tab-pane fade" id="form-tabs-social" role="tabpanel">
                        <div class="row g-3">
                            @foreach($visibility as $visibility_data)
                            <div class="col-md-12">
                                <div class="form-check form-check-primary mt-3">
                                    <input class="form-check-input" type="checkbox" name="visibillity[]" id="visibillity_{{$visibility_data->id}}" value="{{$visibility_data->id}}">
                                    <label class="form-check-label" for="visibillity">{{$visibility_data->display_name}}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <!-- <div class="pt-4">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1">Submit</button>
                        <button type="reset" class="btn btn-label-secondary">Cancel</button>
                        </div> -->
                    
                    </div>
                    <div class="tab-pane fade" id="form-tabs-voting" role="tabpanel">
                        <input type="hidden" id="optionCount" value="1">
                        <div class="form-repeater">
                            <div class="row g-3" >
                                <div class="col-md-12">
                                    <label class="switch switch-square">
                                        <input type="checkbox" class="switch-input" id="is_voting_enable" name="is_voting_enable" onchange="showQuestion('is_voting_enable');">
                                        <span class="switch-toggle-slider">
                                            <span class="switch-on"></span>
                                            <span class="switch-off"></span>
                                        </span>
                                        <span class="switch-label">Enable Voting</span>
                                    </label>
                                </div>
                                <div class="col-md-12 showQuestion hide">
                                    <label class="form-label" for="question">Question</label>
                                    <input type="text" id="question" class="form-control" name="question" placeholder="Enter question" />
                                </div>
                            </div>
                            <div data-repeater-list="group-a" class="showQuestion hide" >
                            <div data-repeater-item>
                                <div class="row">
                                    <div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0">
                                        <label class="form-label" for="option">Option</label>
                                        <input type="text" id="option" class="form-control" name="option" placeholder="Enter Option" />
                                    </div>
                                    <div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0">
                                        <button type="button" onclick="addRemoveOptions('remove');" class="btn btn-label-danger mt-4" data-repeater-delete>
                                        <i class="ti ti-x ti-xs me-1"></i>
                                        <span class="align-middle">Delete</span>
                                        </button>
                                    </div>
                                </div>
                                <hr />
                            </div>
                            </div>
                            <div class="mb-0 showQuestion addOption hide" >
                            <button type="button" onclick="addRemoveOptions('add');" class="btn btn-primary" data-repeater-create>
                                <i class="ti ti-plus me-1"></i>
                                <span class="align-middle">Add Option</span>
                            </button>
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