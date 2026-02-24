<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-dark">
            Delete Account
        </h2>

        <p class="mt-1 text-muted">
            Once your account is deleted, all of its resources and data will be permanently deleted.
        </p>
    </header>

    @if ($errors->userDeletion->get('password'))
        <div class="alert-box alert-box--error">
            {{ $errors->userDeletion->first('password') }}
            <span class="alert-box__close"></span>
        </div>
    @endif

    <button type="button" class="btn btn--primary" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
        Delete Account
    </button>

    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmUserDeletionModalLabel">Are you sure you want to delete your
                            account?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p>
                            Please enter your password to confirm you would like to permanently delete your account.
                        </p>

                        <div class="mb-3">
                            <label for="password">Password</label>
                            <input id="password" name="password" type="password" class="u-fullwidth"
                                placeholder="Password" required />
                            @if ($errors->userDeletion->get('password'))
                                <div class="text-danger mt-2">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn--primary">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
