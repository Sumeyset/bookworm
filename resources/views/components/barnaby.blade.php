{{-- Barnaby the worm: chat bubble on every page. Logic: resources/js/barnaby.js --}}
<div class="barnaby" data-barnaby data-chat-url="{{ route('barnaby.chat') }}" data-user="{{ auth()->id() }}" data-name="{{ Str::before(auth()->user()->name, ' ') }}">
    <button type="button" class="barnaby-fab" data-barnaby-open aria-label="Ask Barnaby, your reading worm" aria-expanded="false" aria-controls="barnaby-panel">
        <x-barnaby-avatar class="barnaby-fab__avatar" />
        <span class="barnaby-fab__label">Ask Barnaby</span>
    </button>

    <section id="barnaby-panel" class="barnaby-panel" data-barnaby-panel hidden role="dialog" aria-labelledby="barnaby-title">
        <header class="barnaby-panel__head">
            <x-barnaby-avatar class="h-10 w-10 shrink-0" />
            <div class="min-w-0 flex-1 leading-tight">
                <h2 id="barnaby-title" class="font-semibold">Barnaby</h2>
                <p class="text-xs opacity-80">your reading worm · <span data-barnaby-remaining></span></p>
            </div>
            <button type="button" class="barnaby-icon" data-barnaby-clear title="Start over" aria-label="Start a new chat">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4v6h6M20 20v-6h-6"/><path d="M5.5 15a7 7 0 0 0 12.4 2.5M18.5 9A7 7 0 0 0 6.1 6.5"/></svg>
            </button>
            <button type="button" class="barnaby-icon" data-barnaby-close aria-label="Close Barnaby">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </header>

        <div class="barnaby-panel__body" data-barnaby-scroll>
            <div class="barnaby-msg barnaby-msg--bot">
                <x-barnaby-avatar class="barnaby-msg__avatar" />
                <div class="barnaby-msg__bubble">
                    <p>Hi {{ Str::before(auth()->user()->name, ' ') }}! I’m <b>Barnaby</b> 🪱 I’ve eaten my way through a lot of books. Tell me about a book you’re in the mood for, or ask me what to read next.</p>
                </div>
            </div>
            <div class="barnaby-chips" data-barnaby-chips>
                <button type="button" data-barnaby-ask="What should I read next, based on my shelf?">📚 What should I read next?</button>
                <button type="button" data-barnaby-prefill="I’m looking for a book that ">✨ Describe a book you want</button>
                <button type="button" data-barnaby-ask="Help me pick my next read from my Want to read list.">🎯 Pick from my Want to read</button>
                <button type="button" data-barnaby-context-ask hidden></button>
            </div>
            <div data-barnaby-log aria-live="polite"></div>
        </div>

        <form class="barnaby-panel__form" data-barnaby-form>
            <label for="barnaby-input" class="sr-only">Message Barnaby</label>
            <textarea id="barnaby-input" rows="1" maxlength="1500" placeholder="Ask Barnaby about books…" data-barnaby-input></textarea>
            <button type="submit" class="barnaby-send" data-barnaby-send aria-label="Send" disabled>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.5 3.5 10.6 13.4M21.5 3.5 14.6 21l-4-7.6-7.6-4 18.5-5.9Z"/></svg>
            </button>
        </form>
    </section>
</div>
