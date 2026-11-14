<x-app-layout>
    <x-slot name="title">Notifications · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="What you missed" title="Notifications">
            <x-slot name="actions">
                <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button class="btn-ghost">Mark all as read</button></form>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section">
        <div class="bw-card max-w-3xl p-3">
            @forelse ($notifications as $notification)
                @include('notifications.partials.item')
            @empty
                <x-empty-state class="border-0" title="All quiet" message="Follows, likes, replies and buddy-read invites will show up here." />
            @endforelse
        </div>
        <div class="mt-6 max-w-3xl">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
