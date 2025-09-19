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
              <h4 class="mb-1 pt-2">{{__('lang.admin_forget_password')}}</h4>
              <p class="mb-4">{{__('lang.admin_forget_password_sub_text')}}</p>

              <form id="formAuthentication" class="mb-3" action="{{ url('/do-admin-forget-password') }}" method="POST">
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
                <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit">{{__('lang.admin_forget')}}</button>
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