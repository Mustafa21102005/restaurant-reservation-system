@extends('layouts.theme')

@section('title', 'Profile')

@section('content')
    <section class="s-pageheader"
        style="background-image: url('{{ asset('theme/images/pageheader/pageheader-about-bg-3000.jpg') }}')">
        <div class="row">
            <div class="column xl-12 s-pageheader__content">
                <h1 class="page-title">
                    Manage Your Account
                </h1>
            </div>
        </div>
    </section>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="mb-4">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="mb-4">
                    @include('profile.partials.update-password-form')
                </div>

                @role('customer')
                    <div class="mb-4">
                        @include('profile.partials.delete-user-form')
                    </div>
                @endrole
            </div>
        </div>
    </div>
@endsection
