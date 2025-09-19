@extends('admin/layout/app')

@section('content')
<link rel="stylesheet" href="{{ asset('admin-assets/vendor/css/pages/page-auth.css')}}"/>
<div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner py-4">
          <!-- Login -->
          <div class="card">
            <div class="card-body">
              <!-- Logo -->
              <div class="app-brand justify-content-center mb-4 mt-2">
                <a href="{{url('/')}}" class="app-brand-link gap-2">
                  <span class="app-brand-text demo text-body fw-bold ms-1">Incite</span>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1 pt-2">{{__('lang.admin_reset_password')}}</h4>
              <p class="mb-4">{{__('lang.admin_reset_password_sub_text')}}</p>

              <form id="formAuthentication" class="mb-3" action="{{ url('/do-admin-reset-password') }}" method="POST">
              @csrf
                <div class="mb-3">
                  <label for="otp" class="form-label">{{__('lang.admin_otp')}}</label>
                  <input
                    type="text"
                    id="otp"
                    class="form-control @error('otp') is-invalid @enderror" name="otp" value="{{ old('otp') }}" required autocomplete="otp" autofocus
                    placeholder="{{__('lang.admin_otp_placeholder')}}"
                  />
                </div>
                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">{{__('lang.admin_password')}}</label>
                    
                  </div>
                  <div class="input-group input-group-merge">
                    <input
                      id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="password"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="password"
                    />
                    <span class="input-group-text cursor-pointer" id="toggle-password"><i id="password-icon" class="ti ti-eye-off"></i></span>
                  </div>
                </div>
                <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password">{{__('lang.admin_cpassword')}}</label>
                    
                  </div>
                  <div class="input-group input-group-merge">
                    <input
                      id="cpassword" type="cpassword" class="form-control @error('v') is-invalid @enderror" name="cpassword" required autocomplete="cpassword"
                      placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                      aria-describedby="cpassword"
                    />
                    <span class="input-group-text cursor-pointer" id="toggle-cpassword"><i id="cpassword-icon" class="ti ti-eye-off"></i></span>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">{{__('lang.admin_reset')}}</button>
                </div>
              </form>
              <div class="text-center">
                <a href="{{url('admin-login')}}" class="d-flex align-items-center justify-content-center">
                  <i class="ti ti-chevron-left scaleX-n1-rtl"></i>
                  {{__('lang.admin_back_to_login')}}
                </a>
              </div>  
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>
    @endsection