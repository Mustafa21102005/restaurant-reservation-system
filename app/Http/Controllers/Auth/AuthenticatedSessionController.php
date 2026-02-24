<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Authenticate the user
        $request->authenticate();

        // Regenerate session for security
        $request->session()->regenerate();

        // Get the authenticated user
        $user = Auth::user();

        // Check if user is banned
        if ($user->hasRole('customer') && $user->isBanned()) {
            Auth::logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been banned. Please check your email.',
            ]);
        }

        // Check if user is in timeout
        if ($user->hasRole('customer') && $user->isTimedOut()) {
            $timeout = $user->timeouts()->where('expires_at', '>', now())->first();
            if ($timeout) {
                $remainingTime = \Carbon\Carbon::parse($timeout->expires_at)->diffForHumans();
                Auth::logout();
                return redirect()->route('login')->withErrors([
                    'email' => "Your account is in timeout. Please try again in {$remainingTime}.",
                ]);
            }
        }

        // Redirect based on role
        if ($user->hasRole('admin')) {
            return redirect('/dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect('/');
        }

        // Optionally handle other roles here
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
