@extends('admin/layout/app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /</span> {{__('lang.admin_profile')}}</h4>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
            <div class="card-body">
                <form method="POST" id="update-record" action="{{url('admin/update-profile')}}" onsubmit="return validateProfile('update-record');" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{$row->id}}">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-3 mb-3 display-inline-block width-32-percent">
                                <label class="form-label" for="name">{{__('lang.admin_name')}} <span class="required">*</span></label>
                                <input type="text" class="form-control" placeholder="{{__('lang.admin_name_placeholder')}}"  name="name" value="{{$row->name}}"/>
                            </div>
                            <div class="col-md-4 mb-3 display-inline-block">
                                <label class="form-label" for="email">{{__('lang.admin_email')}} <span class="required">*</span></label>
                                <input type="email" class="form-control" placeholder="{{__('lang.admin_email_placeholder')}}"  name="email" value="{{$row->email}}" />
                            </div>
                            <div class="col-md-4 mb-3 display-inline-block">
                                <label class="form-label" for="phone">{{__('lang.admin_phone')}} <span class="required">*</span></label>
                                <input type="text" class="form-control" placeholder="{{__('lang.admin_phone_placeholder')}}"  name="phone" value="{{$row->phone}}" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="col-md-4 mb-3 display-inline-block">
                                <label class="form-label" for="password">{{__('lang.admin_password')}}</label>
                                <input type="password" class="form-control" placeholder="{{__('lang.admin_password_placeholder')}}"  name="password"  />
                            </div>
                        </div>

                        <div class="col-sm-12">
                            <div class="mb-1">
                                <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_image')}}</label>
                                <div class="d-flex">
                                <img src="{{url('uploads/user/'.$row->photo)}}" class="rounded me-50" id="image-preview" alt="photo" height="80" width="80"/>
                                <div class="mt-75 ms-1">
                                    <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                    <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                    <input class="form-control" type="file" name="photo" id="change-picture" hidden accept="image/*" name="photo" onclick="showImagePreview('change-picture','image-preview',512,512);" value="{{$row->photo}}"/>
                                    <span class="d-block d-sm-none">
                                        <i class="me-0" data-feather="edit"></i>
                                    </span>
                                    </label>
                                    <p>{{__('lang.admin_profile_image_resolution')}}</p>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                            <a href="{!! url('admin/dashboard') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection