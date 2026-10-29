<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    public function index(Request $request): View
    {
        $followedIds = $request->user()->following()->select('users.id');
        $reviews = \App\Models\Review::query()
            ->with(['user', 'book'])
            ->whereIn('user_id', $followedIds)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('feed.index', compact('reviews'));
    }
}
