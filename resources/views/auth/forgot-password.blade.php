@extends('layouts.auth')

@section('title', 'Forgot-Password')

@section('content')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('home') }}">
                                <h3 class="text-primary me-4">Maillard</h3>
                            </a>
                            <p class="small">Forgot your password? Enter your email address and we'll send you a
                                password reset link.</p>
                        </div>

                        <x-alert-error />

                        @if (session('status'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fa fa-info-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="form-floating mb-3">
                            <input name="email" value="{{ old('email') }}" type="email" class="form-control"
                                id="floatingInput" placeholder="Email Address" required autofocus>
                            <label for="floatingInput">Email address</label>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-3">Send Password Reset
                            Link</button>
                        <a href="{{ route('login') }}" class="btn btn-light py-3 w-100">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
