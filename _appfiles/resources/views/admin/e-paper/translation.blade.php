@extends('admin/layout/app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/live-news')}}"> {{__('lang.admin_epaper')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_translate_epaper')}}</h4>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label" for="language_code">{{__('lang.admin_language')}}</label>
                            <select class="form-select" id="language_code" name="language_code" onchange="selectLanguage(this.value);" required>
                                @foreach($languages as $row)
                                    <option value="{{$row->code}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{url('admin/translation-e-paper/'.$detail->id)}}" enctype="multipart/form-data">
                    @csrf
                    @php $i = 0; @endphp
                    @foreach($languages as $row) 
                        @if($row->details)
                            <input type="hidden" name="translation_id[]" value="{{$row->details->id}}">
                            <input type="hidden" name="language_code[]" value="{{$row->code}}">
                            <div class="row formbody <?php if($i>0){ ?>hide<?php } ?>" id="translation_{{$row->code}}">
                                <div class="col-md-12">
                                    <div class="mb-3 display-inline-block width-49-percent">
                                        <label class="form-label" for="name">{{__('lang.admin_name')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.admin_name_placeholder')}}" value="{{$row->details->name}}" name="name[]" id="name_{{$row->code}}" />
                                    </div>
                                    @if(setting('chat_gpt_api_key')!='')
                                        <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByThirdParty('{{$row->name}}','{{$row->code}}','name_{{$row->code}}');">{{__('lang.chat_gpt_translate')}}</button>
                                    @else
                                        <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="translationKeyNotFoundError();">{{__('lang.chat_gpt_translate')}}</button>
                                    @endif
                                    @if(setting('google_translation_api_key')!='')
                                        <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByGoogle('{{$row->name}}','{{$row->code}}','name_{{$row->code}}');">{{__('lang.google_translate')}}</button>
                                    @else
                                        <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translationKeyNotFoundError();">{{__('lang.google_translate')}}</button>
                                    @endif
                                    
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                    <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_pdf')}}</label>
                                        <div class="d-flex">
                                            @if($row->details->pdf!='')
                                            <a target="_blank" class="rounded me-50" href="{{ asset('uploads/e-paper/pdf')}}/{{$row->details->pdf}}"><i class="ti ti-download me-1 margin-top-negative-4"></i></a>
                                            @endif
                                            <div class="mt-75 ms-1">
                                            <label class="btn btn-primary me-75 mb-0" for="change-pdf_{{$row->id}}">
                                                <span class="d-none d-sm-block">{{__('lang.admin_upload')}}</span>
                                                <input class="form-control" type="file" id="change-pdf_{{$row->id}}" hidden accept=".pdf" name="pdf[]" />
                                                <span class="d-block d-sm-none">
                                                <i class="me-0" data-feather="edit"></i>
                                                </span>
                                            </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @php $i++; @endphp
                        @endif 
                    @endforeach
                    <div class="row">
                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                            <a href="{!! url('admin/e-papers') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection