@extends('layouts.auth')

@section('title', 'Email-Verification')

@section('content')
    <form method="POST" action="{{ route('verification.send') }}">
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

                        @if (session('status') == 'verification-link-sent')
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fa fa-exclamation-circle me-2"></i>A new verification link has been sent.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <p class="mb-4">Thanks for signing up! Before getting started, could you verify your email
                            address by clicking on the link we just emailed you? If you didn't receive the email,click here
                            to send another.</p>

                        <button type="submit" class="btn btn-primary py-3 w-100 mb-4">Resend Verification Email</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
