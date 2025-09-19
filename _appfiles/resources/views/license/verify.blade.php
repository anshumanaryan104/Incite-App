
<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed" dir="ltr" data-theme="theme-default" data-assets-path="{{asset('/admin-assets/')}}" data-template="vertical-menu-template">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
      <title>{{setting('site_seo_title')}}</title>
      <meta name="description" content="" />
      <link rel="icon" type="image/x-icon" href="{{url('uploads/setting/'.setting('site_favicon'))}}" />
      <link rel="preconnect" href="https://fonts.googleapis.com" />
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />    
      <script>
          var base_url = "{{url('')}}";
      </script>
      <link rel="stylesheet" href="{{ asset('admin-assets/font/font.css')}}" />
      <link rel="stylesheet" href="{{ asset('admin-assets/vendor/fonts/icons.css')}}" />
      <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/rtl/theme.css')}}" id="theme-style" />
      <link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/toastr/toastr.css')}}" />
      <link rel="stylesheet" href="{{ asset('admin-assets/vendor/libs/animate-css/animate.css')}}" />
      <link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/pages/page-auth.css')}}"/>
      <script src="{{ asset('admin-assets/vendor/js/helpers.js')}}"></script>
      <script src="{{ asset('admin-assets/js/config.js')}}"></script>
    </head>
    <body> 
        <div class="container-xxl">
            <div class="authentication-wrapper authentication-basic container-p-y">
                <div class="authentication-inner py-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="app-brand justify-content-center mb-4 mt-2">
                                <a href="{{url('/')}}" class="app-brand-link gap-2">
                                    <span class="app-brand-text demo text-body fw-bold ms-1">Incite</span>
                                </a>
                            </div>
                            @if(env('CODE_VERIFIED')==true)
                              <h4 class="mb-1 pt-2">Thank You</h4>
                              <p>Purchased code verified successfully</p>
                              <p class="mb-4">Use this credentials to login admin panel</p>
                              <p class="">
                                  Username : <span id="username">admin@gmail.com</span>
                                  <i class="menu-icon tf-icons ti ti-copy copy-button" data-clipboard-target="#username" style="cursor: pointer;"></i>
                              </p>
                              <p class="">
                                  Password : <span id="password">admin</span>
                                  <i class="menu-icon tf-icons ti ti-copy copy-button" data-clipboard-target="#password" style="cursor: pointer;"></i>
                              </p>
                              <a class="btn btn-primary d-grid w-100" href="{{url('/admin-login')}}">Go to admin panel</a>
                            @else
                                <h4 class="mb-1 pt-2">Verify Purchase Code</h4>
                                <small>Please enter purchase code receive from codecanyon license</small>
                                <p class="mb-4"></p>
                                <form class="mb-3" action="{{ route('license.verify') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="purchase_code" class="form-label">Purchase Code</label>
                                        <input
                                          type="text"
                                          id="purchase_code" 
                                          name="purchase_code"
                                          class="form-control @error('purchase_code') is-invalid @enderror" value="{{ old('purchase_code') }}" required autocomplete="purchase_code" autofocus
                                          placeholder="Enter purchase code"
                                        />
                                    </div>
                                    <div class="mb-3">
                                        <button class="btn btn-primary d-grid w-100" type="submit">Verify</button>
                                    </div>
                                </form>
                            @endif              
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('admin-assets/vendor/libs/jquery/jquery.js')}}"></script>
        <script src="{{ asset('admin-assets/vendor/js/bootstrap.js')}}"></script>
        <script src="{{ asset('admin-assets/vendor/js/menu.js')}}"></script>
        <script src="{{ asset('admin-assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
        <script src="{{ asset('admin-assets/vendor/libs/swiper/swiper.js')}}"></script>
        <script src="{{ asset('admin-assets/vendor/libs/toastr/toastr.js')}}"></script>
        <script src="{{ asset('admin-assets/js/main.js')}}"></script>
        <script src="{{ asset('admin-assets/js/dashboards-analytics.js')}}"></script>
        <script src="{{ asset('admin-assets/js/theme.js')}}"></script>
        <script src="{{ asset('admin-assets/js/custom.js')}}"></script>
        <script src="{{ asset('admin-assets/js/ui-toasts.js')}}"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
        @if(Session::has('error'))
            <script>
                toastr['error']('', "{{ session('error') }}");
            </script>
        @endif
        @if(Session::has('info'))
            <script>
                toastr['info']('', "{{ session('info') }}");
            </script>
        @endif
        @if(Session::has('warning'))
            <script>
                toastr['warning']('', "{{ session('warning') }}");
            </script>
        @endif
        @if(Session::has('success'))
        <script>
            $(document).ready(function() {
                $('#basicModal').modal('show');
            });
        </script>
        @endif
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var clipboard = new ClipboardJS('.copy-button');
                clipboard.on('success', function(e) {
                    e.clearSelection();
                    toastr['success']('', 'Copied to clipboard!');
                });
                clipboard.on('error', function(e) {
                    toastr['error']('', 'Copy failed. Please copy the text manually.');
                });
            });
        </script>
    </body>
</html>