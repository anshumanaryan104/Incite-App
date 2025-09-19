<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar" >
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="ti ti-menu-2 ti-sm"></i>
        </a>
    </div>
    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        @can('add-blog')
        <div class="btn-group" id="dropdown-icon-demo">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" >
                <i class="ti ti-plus me-md-1"></i> {{__('lang.admin_create_blog')}}
            </button>
            <ul class="dropdown-menu">
                <li>
                    <a href="{{url('admin/add-blog/post')}}" class="dropdown-item d-flex align-items-center">{{__('lang.admin_create_post')}}</a>
                </li>
                <li>
                    <a href="{{url('admin/add-blog/quote')}}" class="dropdown-item d-flex align-items-center">{{__('lang.admin_create_quote')}}</a>
                </li>
            </ul>
        </div>
        @endcan
        <!-- @if(setting('website_updates')==true)
        <a href="{{url('update-website')}}" class="btn btn-primary">
            <i class="ti ti-plus me-md-1"></i> {{__('lang.admin_update')}} {{setting('website_updates')}}
        </a>
        @endif -->
        <!-- <div class="navbar-nav align-items-center">
            <div class="nav-item navbar-search-wrapper mb-0">
                <a class="btn btn-secondary btn-primary float-right" href="{{url('admin/add-cms')}}">
                    <span>
                    <i class="ti ti-plus me-md-1"></i>
                    <span class="d-md-inline-block d-none">{{__('lang.admin_create_cms')}}</span>
                    </span>
                </a>
            </div>
        </div> -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                <?php
                    $langList = \Helpers::getAllLangList();

                    if (Session()->has('admin_locale') AND array_key_exists(Session()->get('admin_locale'), config('languages'))) {
                        $langCode = Session()->get('admin_locale');
                    }
                    else { // This is optional as Laravel will automatically set the fallback language if there is none specified
                        $langCode = config('app.fallback_locale');
                    }
                ?>
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <i class="fi fi-{{($langCode=='en')?'us':$langCode}} fis rounded-circle me-1 fs-3"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    @if(count($langList)>0)
                        @foreach($langList as $langRow)
                            <li>
                                <a class="dropdown-item" href="{!! url('/admin/setlang') !!}?lang={{$langRow->code}}" data-language="{{$langRow->code}}">
                                <i class="fi fi-{{($langRow->code=='en')?'us':$langRow->code}} fis rounded-circle me-1 fs-3"></i>
                                <span class="align-middle">{{$langRow->name}}</span>
                                </a>    
                            </li>
                        @endforeach
                    @endif
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link theme-switcher" href="javascript:void(0);" onclick="setTheme();" >
                    @if(isset($_COOKIE['theme']))
                        @if($_COOKIE['theme']=='dark')
                            <i class="ti ti-md ti-sun icon-switch"></i>
                        @else
                            <i class="ti ti-md ti-moon-stars icon-switch"></i>
                        @endif
                    @else
                        <i class="ti ti-md ti-moon-stars icon-switch"></i>
                    @endif               
                </a>
            </li>
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ url('uploads/user/'.Auth::user()->photo)}}" alt class="rounded-circle" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{url('/admin/profile')}}">
                        <div class="d-flex">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar avatar-online">
                                    <img src="{{ url('uploads/user/'.Auth::user()->photo)}}" alt class="rounded-circle" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-image.png') }}`" />
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="fw-semibold d-block">{{Auth::user()->name}}</span>
                                <small class="text-muted">{{Auth::user()->email}}</small>
                            </div>
                        </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{url('/admin/profile')}}">
                            <i class="ti ti-user-check me-2 ti-sm"></i>
                            <span class="align-middle">{{__('lang.admin_my_profile')}}</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="ti ti-logout me-2 ti-sm"></i>
                            <span class="align-middle">{{__('lang.admin_logout')}}</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
    <div class="navbar-search-wrapper search-input-wrapper d-none">
        <input type="text" class="form-control search-input container-xxl border-0" placeholder="Search..." aria-label="Search..." />
        <i class="ti ti-x ti-sm search-toggler cursor-pointer"></i>
    </div>
</nav>