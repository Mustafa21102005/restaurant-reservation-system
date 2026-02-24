@extends('layouts.auth')

@section('title', 'Reset-Password')

@section('content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('home') }}">
                                <h3 class="text-primary me-4">Maillard</h3>
                            </a>
                        </div>

                        <x-alert-error />

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

                        <div class="form-floating mb-4">
                            <input name="password_confirmation" type="password" class="form-control" id="floatingConPass"
                                placeholder="Confirm Password" required>
                            <label for="floatingConPass">Confirm Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Reset Password
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
