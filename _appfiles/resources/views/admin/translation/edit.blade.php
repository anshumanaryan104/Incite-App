@extends('admin/layout/app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4 display-inline-block"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/translation')}}"> {{__('lang.admin_translation')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_edit_translation')}}</h4>
   
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
            <div class="card-body">
                <form method="POST" action="{{url('admin/update-translation')}}" id="edit-record" method="POST" enctype="multipart/form-data">
                    @csrf
                    @if(isset($row->translation) && count($row->translation))
                        @foreach($row->translation as $translation)
                            <input type="hidden" id="id" name="id[]" value="{{$translation->id}}"></input>
                            <input type="hidden" id="language_id" name="language_id[]" value="{{$translation->language_id}}"></input>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="value">@if(isset($translation->language) && $translation->language!=''){{$translation->language->name}}@endif</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.admin_value')}}"  name="value[]" value="{{$translation->value}}" id="value_{{$translation->language->code}}" />
                                    </div>
                                    @if(setting('chat_gpt_api_key')!='')
                                        <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByThirdParty('{{$translation->language->name}}','{{$translation->language->code}}','value_{{$translation->language->code}}');">{{__('lang.chat_gpt_translate')}}</button>
                                    @endif
                                    @if(setting('google_translation_api_key')!='')
                                        <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByGoogle('{{$translation->language->name}}','{{$translation->language->code}}','value_{{$translation->language->code}}');">{{__('lang.google_translate')}}</button>
                                    @endif
                                </div>                        
                            </div>
                        @endforeach
                    @endif
                    <div class="row">
                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">{{__('lang.admin_button_save_changes')}}</button>
                            <a href="{!! url('admin/translation') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection