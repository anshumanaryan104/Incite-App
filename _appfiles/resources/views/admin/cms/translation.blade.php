@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/cms')}}"> {{__('lang.admin_cms')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_translate_cms')}}</h4>
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
                <form method="POST" action="{{url('admin/translation-cms/'.$detail->id)}}">
                    @csrf
                    @php $i = 0; @endphp
                    @foreach($languages as $row) 
                        @if($row->details)
                            <input type="hidden" name="translation_id[]" value="{{$row->details->id}}">
                            <input type="hidden" name="language_code[]" value="{{$row->code}}">
                            <div class="row formbody <?php if($i>0){ ?>hide<?php } ?>" id="translation_{{$row->code}}">
                                <div class="col-md-12">
                                    <div class="mb-3 display-inline-block width-49-percent">
                                        <label class="form-label" for="title">{{__('lang.admin_title')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.admin_title_placeholder')}}"  name="title" value="{{$row->details->title}}" name="title[]" id="title_{{$row->code}}"  />
                                    </div>
                                    @if(setting('chat_gpt_api_key')!='')
                                        <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByThirdParty('{{$row->name}}','{{$row->code}}','title_{{$row->code}}');">{{__('lang.chat_gpt_translate')}}</button>
                                    @else
                                        <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="translationKeyNotFoundError();">{{__('lang.chat_gpt_translate')}}</button>
                                    @endif
                                    @if(setting('google_translation_api_key')!='')
                                        <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByGoogle('{{$row->name}}','{{$row->code}}','title_{{$row->code}}');">{{__('lang.google_translate')}}</button>
                                    @else
                                        <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translationKeyNotFoundError();">{{__('lang.google_translate')}}</button>
                                    @endif
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="title">{{__('lang.admin_description')}}</label>
                                        <textarea class="form-control editors" name="description[]" placeholder="{{__('lang.admin_description_placeholder')}}" value="{{$row->details->description}}" id="description_{{$row->code}}">{{$row->details->description}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <script type="text/javascript">
                                ClassicEditor
                                .create( document.querySelector( '#description_{{$row->code}}' ), {} )
                                .catch( error => {
                                    console.log( error );
                                } );
                            </script>
                            @php $i++; @endphp
                        @endif 
                    @endforeach
                    <div class="row">
                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                            <a href="{!! url('admin/cms') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection