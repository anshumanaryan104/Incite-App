@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/select2/select2.css')}}" />
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/push-notification')}}"> {{__('lang.admin_push_notification')}} {{__('lang.admin_list')}} </a> /</span> {{__('lang.admin_send_notification')}}</h4>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" id="add-record" action="{{url('admin/send-push-notification')}}" onsubmit="return validatePushNotification('add-record');" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="quill_html" name="description"></input>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3 display-inline-block width-74-percent">
                                    <label class="form-label" for="title">{{__('lang.admin_send_to')}} <span class="required">*</span></label>
                                    <select class="form-select" name="send_to" onchange="showDropdownToSelectUser(this.value);">                       
                                        <option value="all_user_with_guest">{{__('lang.admin_all_user_with_guest')}}</option>
                                        <option value="all_user_without_guest">{{__('lang.admin_all_user_without_guest')}}</option>
                                        <option value="only_guest">{{__('lang.admin_only_guest')}}</option>
                                        <option value="specific_user">{{__('lang.admin_specific_user')}}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 hide show_user">
                                <div class="mb-3 display-inline-block width-74-percent">
                                    <label class="form-label" for="title">{{__('lang.admin_user_email')}} <span class="required">*</span></label>
                                    <select id="email" class="select2 form-select email" placeholder="Select Email" name="email[]" multiple>
                                        <option value="">{{__('lang.admin_select_email')}}</option>
                                        @foreach($emails as $email)
                                            <option value="{{$email->id}}">{{$email->email}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3 display-inline-block width-74-percent">
                                    <label class="form-label" for="title">{{__('lang.admin_title')}} <span class="required">*</span></label>
                                    <input type="text" class="form-control" placeholder="{{__('lang.admin_title_placeholder')}}"  name="title"  />
                                </div>
                            </div>                            
                            <div class="col-md-12">
                                <div class="mb-3 display-inline-block width-74-percent">
                                    <label class="form-label" for="title">{{__('lang.admin_description')}}</label>
                                    <textarea class="form-control" name="description" id="editor" placeholder="{{__('lang.admin_description_placeholder')}}" ></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                                <a href="{!! url('admin/push-notification') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                            </div>
                        </div>
                    </form>
                </div>
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
</script>
@endsection