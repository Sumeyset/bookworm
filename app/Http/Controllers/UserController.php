<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $readersQuery = User::query()
            ->where('id', '!=', $request->user()->id);

        if ($query === '') {
            $readersQuery->whereKey([]);
        } else {
            $readersQuery->where(function ($users) use ($query) {
                $users->where('name', 'like', '%' . $query . '%')
                    ->orWhere('username', 'like', '%' . $query . '%');
            });
        }

        $readers = $readersQuery
            ->withCount(['followers', 'reviews'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', compact('query', 'readers'));
    }

    public function show(Request $request, User $user): View
    {
        $user->loadCount(['followers', 'following', 'reviews']);

        $isOwnProfile = $request->user()->id === $user->id;

        $isFollowing = $request->user()
            ->following()
            ->where('users.id', $user->id)
            ->exists();

        $reviews = $user->reviews()
            ->with('book')
            ->latest()
            ->paginate(10);

        return view('users.show', [
            'profile' => $user,
            'reviews' => $reviews,
            'isOwnProfile' => $isOwnProfile,
            'isFollowing' => $isFollowing,
        ]);
    }
}