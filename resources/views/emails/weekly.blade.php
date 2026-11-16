@php($first = Str::before($user->name, ' ') ?: $user->name)
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Your week on Bookworm</title></head>
<body style="margin:0;background:#f7f1ea;font-family:Georgia,'Times New Roman',serif;color:#2a1a1d;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f7f1ea;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#fffdf9;border-radius:20px;overflow:hidden;border:1px solid #eadfce;">
    <tr><td style="background:#4a1220;padding:28px 28px 22px;color:#fff8ef;">
        <p style="margin:0;font:600 11px/1 Arial,sans-serif;letter-spacing:3px;text-transform:uppercase;color:#f2c38b;">Bookworm · your week</p>
        <h1 style="margin:10px 0 0;font-size:28px;line-height:1.15;">Hello {{ $first }} 📚</h1>
    </td></tr>
    <tr><td style="padding:24px 28px;font-family:Arial,sans-serif;font-size:15px;line-height:1.55;">
        @php($m = $digest['mine'])
        <h2 style="margin:0 0 8px;font-family:Georgia,serif;font-size:20px;">Your week</h2>
        @if ($m['days'] || $m['finished'])
            <p style="margin:0 0 6px;">You read on <b>{{ $m['days'] }} {{ Str::plural('day', $m['days']) }}</b>@if ($m['pages']), turned <b>{{ number_format($m['pages']) }} pages</b>@endif @if ($m['minutes'])and listened for <b>{{ intdiv($m['minutes'], 60) }}h {{ $m['minutes'] % 60 }}m</b>@endif.</p>
            @if ($m['streak'] > 1)<p style="margin:0 0 6px;">🔥 You’re on a <b>{{ $m['streak'] }}-day streak</b>. Keep it going!</p>@endif
            @foreach ($m['finished'] as $title)<p style="margin:0 0 6px;">✅ You finished <b>{{ $title }}</b>.</p>@endforeach
        @else
            <p style="margin:0;">A quiet week — your next chapter is waiting.</p>
        @endif

        @if ($digest['friends'])
            <h2 style="margin:22px 0 8px;font-family:Georgia,serif;font-size:20px;">Your friends finished</h2>
            @foreach ($digest['friends'] as $f)
                <p style="margin:0 0 6px;">📖 {{ $f['name'] }} — <a href="{{ $f['url'] }}" style="color:#8b2a3d;font-weight:bold;">{{ $f['title'] }}</a></p>
            @endforeach
        @endif

        @if ($digest['challenges'])
            <h2 style="margin:22px 0 8px;font-family:Georgia,serif;font-size:20px;">This month’s challenges</h2>
            @foreach ($digest['challenges'] as $c)
                <p style="margin:0 0 6px;">{{ $c['emoji'] }} {{ $c['name'] }} — {{ $c['done'] ? '✓ done' : number_format($c['progress']) . ' / ' . number_format($c['goal']) }}</p>
            @endforeach
        @endif

        <p style="margin:26px 0 0;text-align:center;">
            <a href="{{ route('users.show', $user->username) }}" style="display:inline-block;background:#6d1a2b;color:#fff8ef;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:999px;">Open my shelf</a>
        </p>
    </td></tr>
    <tr><td style="padding:16px 28px 22px;font:12px/1.5 Arial,sans-serif;color:#8a6a55;border-top:1px solid #eadfce;">
        You get this because weekly emails are on in your Bookworm settings.
        <a href="{{ $unsubscribeUrl }}" style="color:#8a6a55;">Unsubscribe</a> · <a href="{{ route('profile.edit') }}" style="color:#8a6a55;">Email settings</a>
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
