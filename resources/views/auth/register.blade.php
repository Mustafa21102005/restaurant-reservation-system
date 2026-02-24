@extends('layouts.auth')

@section('title', 'Sign Up')

@section('content')
    <form method="POST" action="{{ route('register') }}">
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

                        <div class="form-floating mb-3">
                            <input name="name" type="text" value="{{ old('name') }}" class="form-control"
                                id="floatingText" placeholder="jhondoe" required autofocus>
                            <label for="floatingText">Name</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="email" value="{{ old('email') }}" name="email" class="form-control"
                                id="floatingInput" placeholder="name@example.com" required>
                            <label for="floatingInput">Email address</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input name="password" type="password" class="form-control" id="floatingPassword"
                                placeholder="Password" required>
                            <label for="floatingPassword">Password</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input name="password_confirmation" type="password" class="form-control" id="floatingConPass"
                                placeholder="Confirm Password" required>
                            <label for="floatingConPass">Confirm Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Sign Up</button>

                        <p class="text-center mb-0">Already have an Account? <a href="{{ route('login') }}">Log In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
