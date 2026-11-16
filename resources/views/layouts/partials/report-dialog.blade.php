{{-- One report dialog for the whole site. Any button with data-report="type:id" opens it. --}}
<dialog class="report-dialog" data-report-dialog aria-labelledby="report-title">
    <form method="POST" action="{{ route('reports.store') }}" data-report-form>
        @csrf
        <input type="hidden" name="type" data-report-type>
        <input type="hidden" name="id" data-report-id>
        <h2 id="report-title" class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">Report <span data-report-what>this</span></h2>
        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">What’s wrong? Reports are private — they won’t know it was you.</p>
        <fieldset class="mt-4 space-y-1.5">
            <legend class="sr-only">Reason</legend>
            @foreach (\App\Models\Report::REASONS as $key => $label)
                <label class="report-dialog__reason">
                    <input type="radio" name="reason" value="{{ $key }}" required class="text-burgundy focus:ring-burgundy">
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </fieldset>
        <label for="report-details" class="mt-4 block text-sm font-semibold text-ink dark:text-[#F4E9E2]">Anything else? <span class="font-normal text-tan">(optional)</span></label>
        <textarea id="report-details" name="details" rows="2" maxlength="1000" class="bw-input mt-1 w-full"></textarea>
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" class="btn-ghost" data-report-cancel>Cancel</button>
            <button class="btn-primary">Send report</button>
        </div>
    </form>
</dialog>
