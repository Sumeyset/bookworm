<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\ReadingSession;
use App\Models\Report;
use App\Models\User;
use App\Support\Barnaby\BarnabyClient;
use App\Support\Pro;
use App\Support\Push;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** One page for whoever runs Bookworm: growth, activity, reports and server health. */
class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $days = collect(range(13, 0))->map(fn ($n) => today()->subDays($n));
        $signups = User::where('created_at', '>=', $days->first())
            ->get(['created_at'])->groupBy(fn ($u) => $u->created_at->toDateString())->map->count();
        $readers = ReadingSession::where('read_on', '>=', $days->first()->toDateString())
            ->get(['read_on', 'user_id'])->groupBy(fn ($s) => Carbon::parse($s->read_on)->toDateString())->map(fn ($g) => $g->pluck('user_id')->unique()->count());

        $chart = $days->map(fn (Carbon $day) => [
            'label' => $day->format('D j'),
            'signups' => (int) ($signups[$day->toDateString()] ?? 0),
            'readers' => (int) ($readers[$day->toDateString()] ?? 0),
        ]);

        $search = trim((string) $request->query('q'));

        return view('admin.index', [
            'stats' => [
                ['Readers', User::count(), '+' . User::where('created_at', '>=', now()->subWeek())->count() . ' this week'],
                ['Active this week', ReadingSession::where('read_on', '>=', now()->subWeek()->toDateString())->distinct()->count('user_id'), 'logged reading'],
                ['Books', Book::count(), Book::whereNull('spine_color')->count() . ' without spine colour'],
                ['Posts', Post::count(), Post::where('created_at', '>=', now()->subWeek())->count() . ' this week'],
                ['Open reports', Report::where('status', 'open')->count(), 'waiting for review'],
                ['Pro readers', User::where('pro_until', '>', now())->count(), Pro::configured() ? 'Stripe connected' : 'Stripe not set up'],
            ],
            'chart' => $chart,
            'chartMax' => max(1, $chart->max('signups'), $chart->max('readers')),
            'health' => $this->health(),
            'search' => $search,
            'users' => User::query()
                ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->latest()->take(15)->get(),
        ]);
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin || $user->is($request->user()), 422);
        $user->forceFill(['suspended_at' => $user->suspended_at ? null : now()])->save();

        return back()->with('status', $user->suspended_at ? "@{$user->username} is suspended." : "@{$user->username} can sign in again.");
    }

    /** @return array<int, array{0: string, 1: bool, 2: string}> [label, ok, detail] */
    private function health(): array
    {
        try {
            DB::select('select 1');
            $db = [true, config('database.default')];
        } catch (\Throwable $e) {
            $db = [false, 'not reachable'];
        }
        $failed = rescue(fn () => DB::table('failed_jobs')->count(), 0, false);
        $pending = rescue(fn () => DB::table('jobs')->count(), 0, false);
        $free = @disk_free_space(storage_path());
        $logSize = is_file($log = storage_path('logs/laravel.log')) ? filesize($log) : 0;

        return [
            ['Database', $db[0], $db[1]],
            ['Queue', $failed === 0, "{$pending} waiting · {$failed} failed"],
            ['Disk space', $free === false || $free > 1024 ** 3, $free === false ? 'unknown' : round($free / 1024 ** 3, 1) . ' GB free'],
            ['Log file', $logSize < 50 * 1024 ** 2, round($logSize / 1024 ** 2, 1) . ' MB'],
            ['Debug mode', ! config('app.debug') || app()->environment('local', 'testing'), config('app.debug') ? 'on (turn off in production)' : 'off'],
            ['HTTPS', str_starts_with((string) config('app.url'), 'https://') || app()->environment('local', 'testing'), (string) config('app.url')],
            ['Email', config('mail.default') !== 'log', config('mail.default') === 'log' ? 'writing to the log only' : config('mail.default')],
            ['Barnaby (AI)', BarnabyClient::configured(), BarnabyClient::configured() ? 'key set' : 'no API key'],
            ['Push notifications', Push::configured(), Push::configured() ? 'VAPID keys set' : 'no VAPID keys'],
            ['Payments', Pro::configured(), Pro::configured() ? 'Stripe keys set' : 'Stripe not set up'],
            ['Versions', true, 'PHP ' . PHP_VERSION . ' · Laravel ' . app()->version()],
        ];
    }
}
