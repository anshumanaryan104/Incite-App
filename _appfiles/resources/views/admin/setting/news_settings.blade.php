@if($row->key == 'live_news_logo')
    <div class="col-md-12 mb-3">
        <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_live_news_image')}}</label>
        <div class="d-flex">
        <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="news-logo-preview" alt="live_news_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
        <div class="mt-75 ms-1">
            <label class="btn btn-primary me-75 mb-0" for="change-news-logo">
            <span class="d-none d-sm-block">{{__('lang.admin_upload_live_news_image')}}</span>
            <input class="form-control" type="file" name="live_news_logo" id="change-news-logo" hidden accept="image/*" name="live_news_logo" onclick="showImagePreview('change-news-logo','news-logo-preview',512,512);"/>
            <span class="d-block d-sm-none">
                <i class="me-0" data-feather="edit"></i>
            </span>
            </label>
            <p>{{__('lang.admin_live_news_image_resolution')}}</p>
        </div>
        </div>
    </div>
@endif
@if($row->key == 'live_news_status')
    <div class="col-md-12 mb-3">
        <div class="form-check form-check-primary mt-3">
            <input class="form-check-input" type="checkbox" name="live_news_status" id="live_news_status" @if($row->value == 1) checked @endif>
            <label class="form-check-label" for="live_news_status">{{__('lang.admin_live_news_status_placeholder')}}</label>
        </div>
    </div>
@endif
@if($row->key == 'e_paper_logo')
    <div class="col-md-12 mb-3">
        <label class="form-label" for="basic-icon-default-uname">{{__('lang.admin_epaper_image')}}</label>
        <div class="d-flex">
        <img src="{{url('uploads/setting/'.$row->value)}}" class="rounded me-50" id="epaper-logo-preview" alt="e_paper_logo" height="80" width="80" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`"/>
        <div class="mt-75 ms-1">
            <label class="btn btn-primary me-75 mb-0" for="change-epaper-logo">
            <span class="d-none d-sm-block">{{__('lang.admin_upload_epaper_image')}}</span>
            <input class="form-control" type="file" name="e_paper_logo" id="change-epaper-logo" hidden accept="image/*" name="e_paper_logo" onclick="showImagePreview('change-epaper-logo','epaper-logo-preview',512,512);"/>
            <span class="d-block d-sm-none">
                <i class="me-0" data-feather="edit"></i>
            </span>
            </label>
            <p>{{__('lang.admin_epaper_image_resolution')}}</p>
        </div>
        </div>
    </div>
@endif
@if($row->key == 'e_paper_status')
    <div class="col-md-12 mb-3">
        <div class="form-check form-check-primary mt-3">
            <input class="form-check-input" type="checkbox" name="e_paper_status" id="e_paper_status" @if($row->value == 1) checked @endif>
            <label class="form-check-label" for="e_paper_status">{{__('lang.admin_e_paper_status_placeholder')}}</label>
        </div>
    </div>
@endif