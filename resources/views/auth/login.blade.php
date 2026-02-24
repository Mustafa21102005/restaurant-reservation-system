@extends('layouts.auth')

@section('title', 'Log In')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('home') }}">
                                <h3 class="text-primary">Maillard</h3>
                            </a>
                        </div>

                        <x-alert-error />

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa fa-check-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="form-floating mb-3">
                            <input name="email" value="{{ old('email') }}" type="email" class="form-control"
                                id="floatingInput" placeholder="name@example.com" required autofocus>
                            <label for="floatingInput">Email address</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" id="floatingPassword"
                                placeholder="Password" required>
                            <label for="floatingPassword">Password</label>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input name="remember" type="checkbox" class="form-check-input" id="exampleCheck1">
                                <label class="form-check-label" for="exampleCheck1">Remember Me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">Forgot Password</a>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Log In</button>

                        <p class="text-center mb-0">Don't have an Account? <a href="{{ route('register') }}">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
