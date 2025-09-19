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
                <a href="{{url('/')}}" class="text-align-center gap-2">
                  <img class="width-45-percent" src="{{url('uploads/setting/'.setting('website_admin_logo'))}}" onerror="this.onerror=null;this.src=`{{ asset('uploads/no-logo-image.png') }}`"/>
                </a>
              </div>
              <!-- /Logo -->
              <h4 class="mb-1 pt-2">{{__('lang.admin_login')}}</h4>
              <p class="mb-4">{{__('lang.admin_login_sub_text')}}</p>

              <form id="formAuthentication" class="mb-3" action="{{ url('/do-login') }}" method="POST">
              @csrf
                <div class="mb-3">
                  <label for="email" class="form-label">{{__('lang.admin_email')}}</label>
                  <input
                    type="email"
                    id="email"
                    class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                    placeholder="{{__('lang.admin_email_placeholder')}}"
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
                  <div class="d-flex justify-content-between">
                    <label class="form-label" for="password"></label>
                    <a href="{{url('admin-forget-password')}}">
                      <small>{{__('lang.admin_forget_password_login')}}</small>
                    </a>
                  </div>
                </div>
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">{{__('lang.login')}}</button>
                </div>
              </form>
            </div>
          </div>
          <!-- /Register -->
        </div>
      </div>
    </div>
    @endsection