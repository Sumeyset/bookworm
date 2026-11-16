<?php

namespace App\Http\Controllers;

use App\Support\Challenges;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChallengeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        Challenges::check($user);

        $badges = $user->badges()->latest()->get();
        $owned = $badges->pluck('key')->flip();

        return view('challenges.index', [
            'challenges' => Challenges::progress($user),
            'daysLeft' => (int) now()->diffInDays(now()->endOfMonth()) + 1,
            'badges' => $badges->filter(fn ($b) => str_starts_with($b->key, 'challenge:'))->values(),
            'milestones' => collect(Challenges::MILESTONES)->map(fn ($m, $key) => [
                'name' => $m[0], 'emoji' => $m[1], 'description' => $m[2], 'earned' => $owned->has($key),
            ]),
            'nextMonth' => collect(Challenges::forMonth(now()->addMonthNoOverflow()))->map(fn ($key) => Challenges::POOL[$key]),
        ]);
    }
}
