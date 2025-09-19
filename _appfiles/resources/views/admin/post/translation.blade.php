@extends('admin/layout/app')
@section('content')
<script src="{{ asset('admin-assets/js/ckeditor.js')}}"></script>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/blog')}}"> Blog {{__('lang.admin_list')}} </a>/</span> Translate @if($detail->type=='post')Blog@else {{ucfirst($detail->type)}} @endif</h4>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-1">
                            <label class="form-label" for="language_code">Select Language</label>
                            <select class="form-select" id="language_code" name="language_code" onchange="selectLanguage(this.value);" required>
                                @foreach($languages as $row)
                                    <option value="{{$row->code}}">{{$row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{url('admin/blog/translation/'.$detail->id)}}">
                    @csrf
                    @php $i = 0; @endphp
                    @foreach($languages as $row) 
                        @if($row->details)
                            <input type="hidden" name="translation_id[]" value="{{$row->details->id}}">
                            <input type="hidden" name="language_code[]" value="{{$row->code}}">
                            <div class="row formbody <?php if($i>0){ ?>hide<?php } ?>" id="translation_{{$row->code}}">
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="title">{{__('lang.title')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.title')}}"  name="title" value="{{$row->details->title}}" name="title[]" id="title_{{$row->code}}"  />
                                    </div>
                                    <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByThirdParty('{{$row->name}}','{{$row->code}}','title_{{$row->code}}');">{{__('lang.translate')}}</button>
                                </div>
                                @if($detail->type=='post')
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="tags">{{__('lang.tags')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.tags')}}"  value="{{$row->details->tags}}" name="tags[]" id="tags_{{$row->code}}"  />
                                    </div>
                                </div>
                                @endif
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="title">{{__('lang.description')}}</label>
                                        <textarea class="form-control" name="description[]" placeholder="{{__('lang.description')}}" value="{{$row->details->description}}" id="description_{{$row->code}}">{{$row->details->description}}</textarea>
                                    </div>
                                </div>
                                @if($detail->type=='post')
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="seo_title">{{__('lang.seo_title')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.seo_title')}}"  value="{{$row->details->seo_title}}" name="seo_title[]" id="seo_title_{{$row->code}}"  />
                                    </div>
                                    <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="translateByThirdParty('{{$row->name}}','{{$row->code}}','seo_title_{{$row->code}}');">{{__('lang.translate')}}</button>
                                </div>
                                @endif
                                @if($detail->type=='post')
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="seo_keyword">{{__('lang.seo_keyword')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.seo_keyword')}}"  value="{{$row->details->seo_keyword}}" name="seo_keyword[]" id="seo_keyword_{{$row->code}}"  />
                                    </div>
                                </div>
                                @endif
                                @if($detail->type=='post')
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="seo_tag">{{__('lang.seo_tag')}}</label>
                                        <input type="text" class="form-control" placeholder="{{__('lang.seo_tag')}}"  value="{{$row->details->seo_tag}}" name="seo_tag[]" id="seo_tag_{{$row->code}}"  />
                                    </div>
                                </div>
                                @endif
                                @if($detail->type=='post')
                                <div class="col-md-8">
                                    <div class="mb-3 display-inline-block width-74-percent">
                                        <label class="form-label" for="seo_description">{{__('lang.seo_description')}}</label>
                                        <textarea class="form-control" name="seo_description[]" placeholder="{{__('lang.seo_description')}}" value="{{$row->details->seo_description}}" id="seo_description_{{$row->code}}">{{$row->details->seo_description}}</textarea>
                                    </div>
                                </div>
                                @endif
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
                            <a href="{!! url('admin/blog') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>
</div>

@endsection