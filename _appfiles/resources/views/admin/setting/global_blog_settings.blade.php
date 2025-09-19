@if($row->key == 'is_voice_enabled')
<div class="col-md-12 mb-3 display-inline-block mr-10">
    <label class="switch switch-square">
        <input type="checkbox" class="switch-input" id="is_voice_enabled" name="is_voice_enabled" onchange="showVoiceType('is_voice_enabled');">
        <span class="switch-toggle-slider">
            <span class="switch-on"></span>
            <span class="switch-off"></span>
        </span>
        <span class="switch-label">{{__('lang.admin_is_voice_enabled_placeholder')}}</span>
    </label>
</div>
@endif
@if($row->key == 'voice_type')
<div class="col-md-12 mb-3 display-inline-block mr-10 showVoiceType hide">
    <div class="form-check form-check-primary mt-3" style="width: fit-content;
    display: inline-block;">
        <input class="form-check-input" type="radio" name="voice_type" id="local_text_to_speech" value="local_text_to_speech" @if($row->value == 'local_text_to_speech') checked  @endif onclick="setTypeOfVoice(this.value);">
        <label class="form-check-label" for="local_text_to_speech">{{__('lang.admin_local_text_to_speech_placeholder')}}</label>
    </div>
    <div class="form-check form-check-primary mt-3" style="width: fit-content;
    display: inline-block;
    margin-left: 40px;">
        <input class="form-check-input" type="radio" name="voice_type" id="google_text_to_speech" value="google_text_to_speech" @if($row->value == 'google_text_to_speech') checked  @endif onclick="setTypeOfVoice(this.value);">
        <label class="form-check-label" for="google_text_to_speech">{{__('lang.admin_google_text_to_speech_placeholder')}}</label>
    </div>
</div>
@endif
@if($row->key == 'blog_language')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10 showgoogletextsettings hide">
    <label class="form-label" for="blog_language">{{__('lang.admin_blog_language')}}</label>
    <select name="blog_language" class="form-control">
        <option value="">{{__('lang.admin_select_blog_language')}}</option>
        @foreach($languages as $lang)
            <option @if($row->value == $lang->code) selected @endif  value="{{$lang->code}}">{{$lang->name}}</option>
        @endforeach
    </select>
</div>
@endif
@if($row->key == 'blog_accent')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10 showgoogletextsettings hide">
    <label class="form-label" for="blog_accent">{{__('lang.admin_blog_accent')}}</label>
    <select name="blog_accent" class="form-control">
        <option value="">{{__('lang.admin_select_blog_accent')}}</option>
        @foreach($voice_accent as $accent)
            <option @if($row->value == $accent) selected  @endif value="{{$accent}}">{{$accent}}</option>
        @endforeach
    </select>
</div>
@endif
@if($row->key == 'blog_voice')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10 showgoogletextsettings hide">
    <label class="form-label" for="blog_voice">{{__('lang.admin_blog_voice')}}</label>
    <select name="blog_voice" class="form-control">
        <option value="">{{__('lang.admin_select_blog_voice')}}</option>
        @foreach(config('constant.speech_voice') as $key => $value)
            <option @if($row->value == $key) selected  @endif value="{{$key}}">{{$value}}</option>
        @endforeach
    </select>
</div>
@endif
@if($row->key == 'google_api_key')
<div class="col-md-3 mb-3 display-inline-block width-32-percent mr-10 showgoogletextsettings hide">
    <label class="form-label" for="google_api_key">{{__('lang.admin_google_api_key')}}</label>
    <input type="text" class="form-control" value="{{$row->value}}" placeholder="{{__('lang.admin_google_api_key_placeholder')}}" name="google_api_key"/>
</div>
@endif
<!-- @if($row->key == 'is_voice_enabled')
<div class="col-md-12 mb-3 display-inline-block width-32-percent mr-10 showgoogletextsettings hide">
    <div class="form-check form-check-primary mt-3">
        <input class="form-check-input" type="checkbox" name="is_voice_enabled" id="is_voice_enabled" @if($row->value == 1) checked  @endif>
        <label class="form-check-label" for="is_voice_enabled">{{__('lang.admin_is_voice_enabled_placeholder')}}</label>
    </div>
</div>
@endif -->