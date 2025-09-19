@extends('vendor.installer.layouts.master')

@section('template_title')
Step 1 | Server Requirements
@endsection

@section('title')
    <i class="fa fa-list-ul fa-fw" aria-hidden="true"></i>
    Server Requirements
@endsection

@section('container')

    @foreach($requirements['requirements'] as $type => $requirement)
        @if($type == 'apache')
            <strong>
                <small>
                    How to enable settings <a href="https://www.keliweb.com/billing/knowledgebase/8555/How-to-set-the-modrewrite-using-cPanel.html" target="_blank">Learn</a> 
                </small>
            </strong>
        @endif
        @if($type == 'config')
            <strong>
                <small>
                    How to enable settings <a href="https://help.canadianwebhosting.com/php/how-do-i-enable-allow-url-fopen#:~:text=You%20can%20modify%20this%20setting,then%20click%20Apply%20then%20Save." target="_blank">Learn</a> 
                </small>
            </strong>
        @endif
        <ul class="list" style="margin: 0px 0 20px;">
            <li class="list__item list__title {{ $phpSupportInfo['supported'] ? 'success' : 'error' }}">
                <strong>{{ ucfirst($type) }}</strong>
                @if($type == 'php')
                    <strong>
                        <small>
                            (version {{ $phpSupportInfo['minimum'] }} required)
                        </small>
                    </strong>
                    <span class="float-right">
                        <strong>
                            {{ $phpSupportInfo['current'] }}
                        </strong>
                        <i class="fa fa-fw fa-{{ $phpSupportInfo['supported'] ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
                    </span>
                @endif
            </li>
            @foreach($requirements['requirements'][$type] as $extention => $enabled)

                <li class="list__item {{ $enabled ? 'success' : 'error' }}">
                    {{ $extention }}
                    <i class="fa fa-fw fa-{{ $enabled ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
                </li>
            @endforeach
        </ul>
    @endforeach

    @if ( ! isset($requirements['errors']) && $phpSupportInfo['supported'] )
        <div class="buttons">
            <a class="button" href="{{ route('LaravelInstaller::permissions') }}">
                Check Permissions
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif

@endsection