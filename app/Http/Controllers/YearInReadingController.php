<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\YearInReading;
use Illuminate\Http\Request;
use Illuminate\View\View;

class YearInReadingController extends Controller
{
    public function show(Request $request, User $user, ?int $year = null): View
    {
        // Members can see each other's recap; anyone can see it if the reader shared it.
        abort_unless($request->user() || $user->is_public, 404);

        $year = $year && $year >= 2000 && $year <= now()->year ? $year : now()->year;

        return view('year.show', [
            'profile' => $user,
            'recap' => YearInReading::for($user, $year),
            'isOwner' => $request->user()?->is($user) ?? false,
        ]);
    }
}
