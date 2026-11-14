<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

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
        $validated = $request->validated();
        $profilePhoto = $request->file('profile_photo');
        unset($validated['profile_photo']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $oldProfilePhotoPath = $user->profile_photo_path;

        if ($profilePhoto) {
            $path = $profilePhoto->store('profile-photos', 'public');

            if ($path === false) {
                throw new RuntimeException('Failed to store the uploaded profile photo.');
            }

            $user->profile_photo_path = $path;
        }

        $user->save();

        if ($profilePhoto && $oldProfilePhotoPath && ! Storage::disk('public')->delete($oldProfilePhotoPath)) {
            throw new RuntimeException('Failed to remove the previous profile photo.');
        }

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

        foreach ($user->bookPdfs()->pluck('path') as $path) {
            if (! Storage::disk('local')->delete($path)) {
                throw new RuntimeException('Failed to remove a private book PDF for the deleted account.');
            }
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
