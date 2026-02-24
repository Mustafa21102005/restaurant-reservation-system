@extends('layouts.auth')

@section('title', 'Confirm-Password')

@section('content')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="container-fluid">
            <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
                <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="bg-secondary rounded p-4 p-sm-5 my-4 mx-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <a href="{{ route('home') }}">
                                <h3 class="text-primary me-4">Maillard</h3>
                            </a>
                            <p class="small">This is a secure area of the application. Please confirm your password before
                                continuing.</p>
                        </div>

                        <x-alert-error />

                        <div class="form-floating mb-4">
                            <input type="password" name="password" class="form-control" id="floatingPassword"
                                placeholder="Password" required autofocus>
                            <label for="floatingPassword">Password</label>
                        </div>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Confirm</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
