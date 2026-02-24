<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            Profile Information
        </h2>

        <p class="mt-1 text-muted">
            Update your account's profile information and email address.
        </p>
    </header>

    @if ($errors->any())
        <div class="alert-box alert-box--error">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            <span class="alert-box__close"></span>
        </div>
    @endif

    @if (session('status') === 'profile-updated')
        <div class="alert-box alert-box--success">
            <p>Profile updated successfully.</p>
            <span class="alert-box__close"></span>
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" class="u-fullwidth"
                value="{{ old('name', $user->name) }}" required autocomplete="name">
        </div>

        <div class="mb-3">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" class="u-fullwidth"
                value="{{ old('email', $user->email) }}" required autocomplete="username">

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-muted">
                        Your email address is unverified.

                        <button form="send-verification" class="btn btn--primary">
                            Click here to send the verification email.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <div class="alert-box alert-box--success">
                            <p> A new verification link has been sent to your email address.
                            </p>
                            <span class="alert-box__close"></span>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <button type="submit" class="btn btn--primary">Save</button>
    </form>
</section>
