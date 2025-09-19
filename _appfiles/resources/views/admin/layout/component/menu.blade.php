<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{url('/admin/dashboard')}}" class="app-brand-link">
            <!-- <span class="app-brand-text demo menu-text fw-bold">Incite</span> -->
            <img class="width-80-percent" src="{{url('uploads/setting/'.setting('website_admin_logo'))}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-logo-image.png') }}`"/>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>
    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{(Request::is('admin/dashboard*') || Request::segment(2)=='') ? 'active' : ''}}">
            <a href="{{url('admin/dashboard')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-smart-home"></i>
            <div data-i18n="{{__('lang.admin_dashboard')}}">{{__('lang.admin_dashboard')}}</div>
            </a>
        </li>
        @can('category')
        <li class="menu-item {{Request::is('admin/category*') ? 'active' : ''}}">
            <a href="{{url('admin/category')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-category"></i>
            <div data-i18n="{{__('lang.admin_categories')}}">{{__('lang.admin_categories')}}</div>
            </a>
        </li>
        @endcan
        @canany(['blog'])
        <li class="menu-item {{(Request::is('admin/blog*') || Request::is('admin/create-blog*')) ? 'active open' : ''}} ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-article"></i>
                <div data-i18n="{{__('lang.admin_blogs')}}">{{__('lang.admin_blogs')}}</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">3</div>
            </a>
            <ul class="menu-sub">
                @can('add-blog')
                <li class="menu-item {{(Request::is('admin/blog/create*')) ? 'active open' : ''}}">
                  <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <div data-i18n="{{__('lang.admin_create_blog')}}">{{__('lang.admin_create_blog')}}</div>
                  </a>
                  <ul class="menu-sub">
                    <li class="menu-item">
                      <a href="{{url('admin/add-blog/post')}}" class="menu-link">
                        <div data-i18n="{{__('lang.admin_create_post')}}">{{__('lang.admin_create_post')}}</div>
                      </a>
                    </li>
                    <li class="menu-item">
                      <a href="{{url('admin/add-blog/quote')}}" class="menu-link">
                        <div data-i18n="{{__('lang.admin_create_quote')}}">{{__('lang.admin_create_quote')}}</div>
                      </a>
                    </li>
                  </ul>
                </li>
                @endcan
                @can('blog')
                <li class="menu-item {{Request::is('admin/blog') ? 'active' : ''}}">
                    <a href="{{url('admin/blog')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_blogs')}}">{{__('lang.admin_blogs')}}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany
        @canany(['rss-feeds'])
        <li class="menu-item {{(Request::is('admin/rss-feeds*') || Request::is('admin/rss-feed-items*')) ? 'active open' : ''}} ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-rss"></i>
                <div data-i18n="{{__('lang.admin_blogs')}}">{{__('lang.admin_rss')}}</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">2</div>
            </a>
            <ul class="menu-sub">
                @can('rss-feeds')
                <li class="menu-item {{Request::is('admin/rss-feeds') ? 'active' : ''}}">
                    <a href="{{url('admin/rss-feeds')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_rss_feeds')}}">{{__('lang.admin_rss_feeds')}}</div>
                    </a>
                </li>
                @endcan
                @can('rss-feed-items')
                <li class="menu-item {{Request::is('admin/rss-feed-items') ? 'active' : ''}}">
                    <a href="{{url('admin/rss-feed-items')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_rss_feed_items')}}">{{__('lang.admin_rss_feed_items')}}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany
        @can('ads')
        <li class="menu-item {{(Request::is('admin/ads*') || Request::is('admin/add-ad*') || Request::is('admin/update-ad*')) ? 'active' : ''}}">
            <a href="{{url('admin/ads')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-ad"></i>
            <div data-i18n="{{__('lang.admin_ads')}}">{{__('lang.admin_ads')}}</div>
            </a>
        </li>
        @endcan
        @can('news-api')
        <li class="menu-item {{Request::is('admin/news-api*') ? 'active' : ''}}">
            <a href="{{url('admin/news-api')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-news"></i>
            <div data-i18n="{{__('lang.admin_news_api')}}">{{__('lang.admin_news_api')}}</div>
            </a>
        </li>
        @endcan
        @can('live-news')
        <li class="menu-item {{(Request::is('admin/live-news*') || Request::is('admin/translation-live-news*')) ? 'active' : ''}}">
            <a href="{{url('admin/live-news')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-broadcast"></i>
            <div data-i18n="{{__('lang.admin_live_news')}}">{{__('lang.admin_live_news')}}</div>
            </a>
        </li>
        @endcan
        @can('e-papers')
        <li class="menu-item {{(Request::is('admin/e-papers*') || Request::is('admin/translation-e-paper*')) ? 'active' : ''}}">
            <a href="{{url('admin/e-papers')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-file-description"></i>
            <div data-i18n="{{__('lang.admin_epapers')}}">{{__('lang.admin_epapers')}}</div>
            </a>
        </li>
        @endcan
        @can('visibility')
        <li class="menu-item {{(Request::is('admin/visibility*') || Request::is('admin/translation-visibility*')) ? 'active' : ''}}">
            <a href="{{url('admin/visibility')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-mist"></i>
            <div data-i18n="{{__('lang.admin_visibility')}}">{{__('lang.admin_visibility')}}</div>
            </a>
        </li>
        @endcan
        @can('cms')
        <li class="menu-item {{(Request::is('admin/cms*') || Request::is('admin/update-cms*') || Request::is('admin/add-cms*') || Request::is('admin/translation-cms*')) ? 'active' : ''}}">
            <a href="{{url('admin/cms')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-writing"></i>
            <div data-i18n="{{__('lang.admin_cms')}}">{{__('lang.admin_cms')}}</div>
            </a>
        </li>
        @endcan
        @can('send-push-notification')
        <li class="menu-item {{(Request::is('admin/send-push-notification*') || Request::is('admin/push-notification*')) ? 'active' : ''}}">
            <a href="{{url('admin/push-notification')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-bell"></i>
            <div data-i18n="{{__('lang.admin_send_notification')}}">{{__('lang.admin_send_notification')}}</div>
            </a>
        </li>
        @endcan
        @can('search-log')
        <li class="menu-item {{Request::is('admin/search-log*') ? 'active' : ''}}">
            <a href="{{url('admin/search-log')}}" class="menu-link">
            <i class="menu-icon tf-icons ti ti-search"></i>
            <div data-i18n="{{__('lang.admin_search_log')}}">{{__('lang.admin_search_log')}}</div>
            </a>
        </li>
        @endcan
        @canany(['user', 'sub-admin', 'role'])
        <li class="menu-item {{(Request::is('admin/user*') || Request::is('admin/sub-admin*') || Request::is('admin/role*')) ? 'active open' : ''}} ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-users    "></i>
                <div data-i18n="{{__('lang.admin_users')}}">{{__('lang.admin_users')}}</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">3</div>
            </a>
            <ul class="menu-sub">
                @can('user')
                <li class="menu-item {{Request::is('admin/user*') ? 'active' : ''}}">
                    <a href="{{url('admin/user')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_users')}}">{{__('lang.admin_users')}}</div>
                    </a>
                </li>
                @endcan
                @can('sub-admin')
                <li class="menu-item {{Request::is('admin/sub-admin*') ? 'active' : ''}}">
                    <a href="{{url('admin/sub-admin')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_subadmins')}}">{{__('lang.admin_subadmins')}}</div>
                    </a>
                </li>
                @endcan
                @can('role')
                <li class="menu-item {{Request::is('admin/role*') ? 'active' : ''}}">
                    <a href="{{url('admin/role')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_roles_and_permissions')}}">{{__('lang.admin_roles_and_permissions')}}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany
        @canany(['settings', 'social-media'])
        <li class="menu-item {{(Request::is('admin/settings*') || Request::is('admin/social-media*')) ? 'active open' : ''}} ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-settings"></i>
                <div data-i18n="{{__('lang.admin_settings')}}">{{__('lang.admin_settings')}}</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">2</div>
            </a>
            <ul class="menu-sub">
                @can('settings')
                <li class="menu-item {{Request::is('admin/settings/all-setting*') ? 'active' : ''}}">
                    <a href="{{url('admin/settings/all-setting')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_all_settings')}}">{{__('lang.admin_all_settings')}}</div>
                    </a>
                </li>
                @endcan
                @can('social-media')
                <li class="menu-item {{Request::is('admin/social-media*') ? 'active' : ''}}">
                    <a href="{{url('admin/social-media')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_social_media')}}">{{__('lang.admin_social_media')}}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany
        @canany(['language', 'translation'])
        <li class="menu-item {{(Request::is('admin/languages*') || Request::is('admin/translation*') || Request::is('admin/update-translation*')) ? 'active open' : ''}} ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-language"></i>
                <div data-i18n=">{{__('lang.admin_localization')}}">{{__('lang.admin_localization')}}</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">2</div>
            </a>
            <ul class="menu-sub">
                @can('language')
                <li class="menu-item {{Request::is('admin/languages*') ? 'active' : ''}}">
                    <a href="{{url('admin/languages')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_languages')}}">{{__('lang.admin_languages')}}</div>
                    </a>
                </li>
                @endcan
                @can('translation')
                <li class="menu-item {{(Request::is('admin/translation*') || Request::is('admin/update-translation*')) ? 'active' : ''}}">
                    <a href="{{url('admin/translation')}}" class="menu-link">
                    <div data-i18n="{{__('lang.admin_translations')}}">{{__('lang.admin_translations')}}</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        @endcanany
    </ul>
</aside>