<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Check if the user is banned
        if ($user->isBanned()) {
            return Redirect::route('profile.edit')->withErrors(['status' => 'Your account is banned and cannot be updated.']);
        }

        // Check if the user is in timeout
        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                return Redirect::route('profile.edit')->withErrors(['status' => 'Your account is in timeout until ' . $timeout->expires_at . ' and cannot be updated.']);
            }
        }

        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Check if the user is banned
        if ($user->isBanned()) {
            return Redirect::route('profile.edit')->withErrors([
                'status' => 'Your account is banned and cannot be deleted.'
            ]);
        }

        // Check if the user is in timeout
        if ($user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                return Redirect::route('profile.edit')->withErrors([
                    'status' => 'Your account is in timeout until ' . $timeout->expires_at . ' and cannot be deleted.'
                ]);
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
