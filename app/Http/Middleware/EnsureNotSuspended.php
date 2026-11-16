<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** A suspended account is signed out, and can't sign back in. */
class EnsureNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->suspended_at) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'This account has been suspended for breaking the community rules.']);
        }

        return $next($request);
    }
}
