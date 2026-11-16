<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Settings beyond the profile form: reading reminders (and privacy). */
class SettingsController extends Controller
{
    public function reminders(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'streak_reminder' => ['nullable', 'boolean'],
            'reminder_hour' => ['required', 'integer', 'between:0,23'],
            'timezone' => ['nullable', 'timezone:all'],
        ]);

        $request->user()->forceFill([
            'streak_reminder' => $request->boolean('streak_reminder'),
            'weekly_email' => $request->boolean('weekly_email'),
            'reminder_hour' => (int) $validated['reminder_hour'],
            'timezone' => $validated['timezone'] ?? $request->user()->timezone,
        ])->save();

        return back()->with('status', 'reminders-updated');
    }
}
