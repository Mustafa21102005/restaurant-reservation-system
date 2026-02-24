<section>
    <header>
        <h2 class="text-lg font-medium text-dark">
            Update Password
        </h2>

        <p class="mt-1 text-muted">
            Ensure your account is using a long, random password to stay secure.
        </p>
    </header>

    @if ($errors->updatePassword->any())
        <div class="alert-box alert-box--error">
            @foreach ($errors->updatePassword->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            <span class="alert-box__close"></span>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="alert-box alert-box--success">
            <p>Password updated successfully.</p>
            <span class="alert-box__close"></span>
        </div>
    @endif

    <form method="post" action="{{ route('password.update') }}" class="mt-4">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password">
                Current Password
            </label>
            <input id="update_password_current_password" name="current_password" type="password" class="u-fullwidth"
                autocomplete="current-password" />
        </div>

        <div class="mb-3">
            <label for="update_password_password">
                New Password
            </label>
            <input id="update_password_password" name="password" type="password" class="u-fullwidth"
                autocomplete="new-password" />
        </div>

        <div class="mb-3">
            <label for="update_password_password_confirmation">
                Confirm Password
            </label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="u-fullwidth" autocomplete="new-password" />
        </div>

        <div class="d-flex align-items-center gap-4">
            <button type="submit" class="btn btn--primary">Save</button>
        </div>
    </form>
</section>
