<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public const GENRES = [
        'Literary fiction', 'Fantasy', 'Mystery', 'Science fiction',
        'Romance', 'History', 'Biography', 'Poetry', 'Nonfiction',
        'Young adult', 'Horror', 'Classics',
    ];

    public function show(Request $request): View
    {
        return view('onboarding.show', [
            'genres' => self::GENRES,
            'user' => $request->user(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_genres' => ['nullable', 'array', 'max:5'],
            'preferred_genres.*' => ['required', 'string', 'in:' . implode(',', self::GENRES)],
            'yearly_reading_goal' => ['required', 'integer', 'between:1,365'],
        ]);

        $request->user()->forceFill([
            'preferred_genres' => array_values(array_unique($validated['preferred_genres'] ?? [])),
            'yearly_reading_goal' => $validated['yearly_reading_goal'],
            'onboarding_completed_at' => now(),
        ])->save();

        return to_route('books.index')->with('status', 'Your reading nook is ready.');
    }

    public function skip(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();

        return to_route('books.index')->with('status', 'You can set your reading preferences any time from your profile.');
    }
}
