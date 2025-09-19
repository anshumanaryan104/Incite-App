@extends('admin/layout/app')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light"><a href="{{url('admin/dashboard')}}">{{__('lang.admin_dashboard')}}</a> /<a href="{{url('admin/category')}}"> {{__('lang.admin_rss_feeds')}} {{__('lang.admin_list')}} </a>/</span> {{__('lang.admin_check_rss_feed')}}</h4>
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3 display-inline-block width-74-percent">
                                <strong>{{__('lang.admin_title')}}</strong>
                                @if(isset($row['title']) && $row['title']!='')
                                    <i class="ti ti-square-check margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: green;"></i>
                                @else
                                    <i class="ti ti-square-x margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: red;"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 display-inline-block width-74-percent">
                                <strong>{{__('lang.admin_description')}}</strong>
                                @if(isset($row['description']) && $row['description']!='')
                                    <i class="ti ti-square-check margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: green;"></i>
                                @else
                                    <i class="ti ti-square-x margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: red;"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 display-inline-block width-74-percent">
                                <strong>{{__('lang.admin_link')}}</strong>
                                @if(isset($row['link']) && $row['link']!='')
                                    <i class="ti ti-square-check margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: green;"></i>
                                @else
                                    <i class="ti ti-square-x margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: red;"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 display-inline-block width-74-percent">
                                <strong>{{__('lang.admin_pub_date')}}</strong>
                                @if(isset($row['pubDate']) && $row['pubDate']!='')
                                    <i class="ti ti-square-check margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: green;"></i>
                                @else
                                    <i class="ti ti-square-x margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: red;"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3 display-inline-block width-74-percent">
                                <strong>{{__('lang.admin_image')}}</strong>
                                @if(isset($row['image']) && $row['image']!='')
                                    <i class="ti ti-square-check margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: green;"></i>
                                @else
                                    <i class="ti ti-square-x margin-top-negative-4" style="font-size: 25px;font-weight: bold;color: red;"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                            <a href="{!! url('admin/rss-feeds') !!}" class="btn btn-outline-secondary">{{__('lang.admin_button_back')}}</a>
                        </div>
                    </div>
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