<?php

namespace App\Http\Controllers;

use App\Models\BookClubPost;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Post;
use App\Models\Report;
use App\Models\Review;
use App\Models\User;
use App\Notifications\BookwormNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Readers report things; admins review them. */
class ReportController extends Controller
{
    /** type => model class */
    public const TYPES = [
        'post' => Post::class,
        'comment' => Comment::class,
        'review' => Review::class,
        'message' => Message::class,
        'club_post' => BookClubPost::class,
        'user' => User::class,
    ];

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'details' => ['nullable', 'string', 'max:1000'],
        ]);
        $me = $request->user();
        $item = self::TYPES[$validated['type']]::findOrFail($validated['id']);
        $owner = $item instanceof User ? $item : $item->user;
        abort_if($owner && $owner->is($me), 422, 'You can’t report yourself.');

        // Messages can only be reported by someone in the chat.
        if ($item instanceof Message) {
            abort_unless($item->conversation->membership($me), 404);
        }

        $report = Report::firstOrCreate(
            ['reporter_id' => $me->id, 'reportable_type' => $validated['type'], 'reportable_id' => $item->getKey(), 'status' => 'open'],
            [
                'reported_user_id' => $owner?->id,
                'reason' => $validated['reason'],
                'details' => $validated['details'] ?? null,
                'excerpt' => Str::limit(self::describe($item), 480),
            ],
        );

        if ($report->wasRecentlyCreated) {
            User::where('is_admin', true)->get()->each(fn (User $admin) => BookwormNotification::send(
                $admin, 'report', 'New report: ' . Report::REASONS[$validated['reason']] . ' (' . str_replace('_', ' ', $validated['type']) . ').', route('admin.reports'),
            ));
        }

        $message = 'Thanks for telling us. We’ll take a look — you can also block ' . ($owner ? '@' . $owner->username : 'them') . ' so you don’t see them.';

        return $request->expectsJson() ? response()->json(['message' => $message]) : back()->with('status', $message);
    }

    /* ---------------- admin ---------------- */

    public function index(Request $request): View
    {
        abort_unless($request->user()->is_admin, 403);
        $status = in_array($request->query('status'), ['open', 'dismissed', 'actioned'], true) ? $request->query('status') : 'open';

        return view('admin.reports', [
            'reports' => Report::with(['reporter', 'reportedUser'])->where('status', $status)->latest()->paginate(30)->withQueryString(),
            'status' => $status,
            'counts' => Report::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function resolve(Request $request, Report $report): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($admin->is_admin, 403);
        $validated = $request->validate(['action' => ['required', Rule::in(['dismiss', 'remove', 'suspend', 'unsuspend'])]]);

        $item = (self::TYPES[$report->reportable_type] ?? null)::find($report->reportable_id);
        $note = match ($validated['action']) {
            'dismiss' => 'Report dismissed.',
            'remove' => $item && ! $item instanceof User ? tap('Content removed.', fn () => $item->delete()) : 'Nothing to remove.',
            'suspend' => $report->reportedUser && ! $report->reportedUser->is_admin
                ? tap('@' . $report->reportedUser->username . ' is suspended.', fn () => $report->reportedUser->forceFill(['suspended_at' => now()])->save())
                : 'That account can’t be suspended.',
            'unsuspend' => $report->reportedUser
                ? tap('@' . $report->reportedUser->username . ' can sign in again.', fn () => $report->reportedUser->forceFill(['suspended_at' => null])->save())
                : 'No account.',
        };

        // Close every open report about the same thing.
        Report::where('reportable_type', $report->reportable_type)->where('reportable_id', $report->reportable_id)->where('status', 'open')
            ->update(['status' => $validated['action'] === 'dismiss' ? 'dismissed' : 'actioned', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        if ($validated['action'] === 'unsuspend') {
            $report->update(['status' => 'actioned', 'resolved_by' => $admin->id, 'resolved_at' => now()]);
        }

        return back()->with('status', $note);
    }

    private static function describe($item): string
    {
        return match (true) {
            $item instanceof User => '@' . $item->username . ' — ' . $item->name . ($item->bio ? ': ' . $item->bio : ''),
            $item instanceof Post => trim(($item->quote_text ? '“' . $item->quote_text . '” ' : '') . ($item->body ?? '')) ?: '(photo post)',
            $item instanceof Message => $item->body ?? '(attachment)',
            default => (string) ($item->body ?? ''),
        };
    }
}
