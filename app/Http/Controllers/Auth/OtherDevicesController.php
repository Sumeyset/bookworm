<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** "Sign out everywhere else": ends every session except this one. */
class OtherDevicesController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('otherDevices', ['current_password' => ['required', 'current_password']]);
        Auth::logoutOtherDevices($request->input('current_password'));
        $request->user()->setRememberToken(\Illuminate\Support\Str::random(60));
        $request->user()->save();

        return back()->with('status', 'other-devices-logged-out');
    }
}
