@extends('vendor.installer.layouts.master')

@section('template_title')
    Step 2 | Permissions
@endsection

@section('title')
    <i class="fa fa-key fa-fw" aria-hidden="true"></i>
    Permissions
@endsection

@section('container')
    <strong>
        <small>
            How to provide permissions to folders <a href="https://www.linkedin.com/pulse/how-assign-permissions-files-folders-cpanel-clement-mensah/?trk=public_post" target="_blank">Learn</a> 
        </small>
    </strong>
    <ul class="list" style="margin: 10px 0 20px;">
        @foreach($permissions['permissions'] as $permission)
        <li class="list__item list__item--permissions {{ $permission['isSet'] ? 'success' : 'error' }}">
            {{ $permission['folder'] }}
            <span>
                <i class="fa fa-fw fa-{{ $permission['isSet'] ? 'check-circle-o' : 'exclamation-circle' }}"></i>
                {{ $permission['permission'] }}
            </span>
        </li>
        @endforeach
    </ul>

    @if ( ! isset($permissions['errors']))
        <div class="buttons">
            <a href="{{ route('LaravelInstaller::environment') }}" class="button">
            Configure Environment
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif

@endsection
