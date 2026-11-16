        {{-- Reader hero --}}
        <section aria-labelledby="reader-profile-heading" class="bw-card relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-wine-header opacity-95" aria-hidden="true"></div>
            <div class="pointer-events-none absolute right-6 top-4 font-hand text-5xl text-[#F2C38B]/25" aria-hidden="true">❦</div>
            <div class="relative flex flex-col gap-5 px-5 pb-6 pt-16 sm:flex-row sm:items-end sm:px-8 sm:pt-32">
                <x-avatar :src="$profile->profile_photo_url" :name="$profile->name" size="lg" class="ring-4 ring-paper dark:ring-raised sm:-mt-16 sm:self-start" />
                <div class="min-w-0 flex-1">
                    <p class="eyebrow">{{ $isOwnProfile ? 'Your reading life' : 'Bookworm reader' }}</p>
                    <h1 id="reader-profile-heading" class="font-display mt-1 break-words text-4xl leading-tight text-ink dark:text-[#f4e9e2] sm:text-5xl font-bold">{{ $profile->name }}</h1>
                    <p class="mt-1 text-sm text-tan dark:text-[#c1aaa0]">{{ '@' . $profile->username }}{{ $profile->is_private ? ' · 🔒 private' : '' }} · reading since {{ $profile->created_at->format('F Y') }}</p>
                </div>
                <div class="shrink-0">
                    @if ($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="btn-ghost">Edit profile and photo</a>
                    @else
                        <div class="flex flex-wrap items-center gap-2">
                            @if ($isFollowing)
                                <form method="POST" action="{{ route('users.unfollow', $profile->username) }}">@csrf @method('DELETE')<button type="submit" class="btn-ghost">Following · Unfollow</button></form>
                            @elseif ($requested ?? false)
                                <form method="POST" action="{{ route('users.unfollow', $profile->username) }}">@csrf @method('DELETE')<button type="submit" class="btn-ghost">Requested · Cancel</button></form>
                            @else
                                <form method="POST" action="{{ route('users.follow', $profile->username) }}">@csrf<button type="submit" class="btn-primary">{{ $profile->is_private ? 'Ask to follow' : 'Follow reader' }}</button></form>
                            @endif
                            <a href="{{ route('messages.start', $profile->username) }}" class="btn-ghost">Message</a>
                            <div class="relative" x-data="{ open: false }">
                                <button type="button" class="btn-ghost !px-3" @click="open = !open" @click.outside="open = false" :aria-expanded="open" aria-label="More">•••</button>
                                <div x-show="open" x-cloak x-transition.origin.top.right class="absolute right-0 z-20 mt-1 w-52 overflow-hidden rounded-2xl border border-warmborder bg-paper py-1 text-sm shadow-xl dark:border-[#4B3035] dark:bg-raised">
                                    <button type="button" data-report="user:{{ $profile->id }}" data-report-name="{{ '@' . $profile->username }}" @click="open = false" class="block w-full px-4 py-2.5 text-left hover:bg-parchment dark:hover:bg-white/5">Report…</button>
                                    <form method="POST" action="{{ route('users.block', $profile->username) }}" onsubmit="return confirm('Block {{ '@' . $profile->username }}? They won’t be able to see your profile, follow you or message you. They won’t be told.')">
                                        @csrf
                                        <button class="block w-full px-4 py-2.5 text-left font-semibold text-red-700 hover:bg-parchment dark:text-red-300 dark:hover:bg-white/5">Block</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="relative grid gap-5 border-t border-warmborder px-5 py-5 dark:border-[#4B3035] sm:grid-cols-[1fr_auto] sm:items-center sm:px-8">
                <div class="min-w-0 space-y-3">
                    <p class="whitespace-pre-line break-words font-serif text-lg italic leading-8 text-walnut dark:text-[#e3d3ca]">{{ $profile->bio ?: 'No introduction yet.' }}</p>
                    @php $profileBadges = ($locked ?? false) ? collect() : $profile->badges()->latest()->take(8)->get(); @endphp
                    @if ($profileBadges->isNotEmpty() || $isOwnProfile)
                        <div class="badge-strip" aria-label="Badges">
                            @foreach ($profileBadges as $badge)
                                <span class="badge-chip" title="{{ $badge->description }}"><span aria-hidden="true">{{ $badge->emoji }}</span>{{ $badge->name }}</span>
                            @endforeach
                            @if ($isOwnProfile)
                                <a href="{{ route('challenges.index') }}" class="badge-chip">🎯 {{ $profileBadges->isEmpty() ? 'Earn your first badge' : 'Challenges' }}</a>
                                <a href="{{ route('year.show', $profile->username) }}" class="badge-chip badge-chip--year">✨ {{ now()->year }} in books</a>
                            @endif
                        </div>
                    @endif
                </div>
                <dl class="grid grid-cols-3 gap-2 text-center">
                    @foreach (['Posts' => $profile->posts_count, 'Followers' => $profile->followers_count, 'Following' => $profile->following_count] as $label => $value)
                        <div class="rounded-2xl bg-parchment/70 px-4 py-2.5 dark:bg-white/5">
                            <dd class="font-display text-3xl text-ink dark:text-[#f4e9e2] font-bold">{{ $value }}</dd>
                            <dt class="font-hand text-lg leading-none text-tan">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>
