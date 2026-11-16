<?php

namespace App\Http\Controllers;

use App\Support\ReadingImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

/**
 * Import a reading export in two steps: upload and see a preview of what
 * will happen, then confirm. The file waits in private storage in between.
 */
class ImportController extends Controller
{
    public function show(): View
    {
        return view('import.show');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,tsv', 'max:5120'],
        ], ['file.mimes' => 'Please upload a .csv (or tab-separated .tsv) file.']);

        try {
            ReadingImport::detectFile($request->file('file')->getRealPath());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        // One waiting file per reader.
        $folder = 'imports/' . $request->user()->id;
        Storage::disk('local')->deleteDirectory($folder);
        $token = Str::random(32);
        Storage::disk('local')->putFileAs($folder, $request->file('file'), $token . '.csv');

        return to_route('import.preview', $token);
    }

    public function preview(Request $request, string $token): View|RedirectResponse
    {
        $path = $this->path($request, $token);
        if (! $path) {
            return to_route('import.show')->withErrors(['file' => 'That upload has expired. Please choose the file again.']);
        }

        return view('import.preview', [
            'token' => $token,
            'preview' => ReadingImport::preview($request->user(), $path),
        ]);
    }

    public function confirm(Request $request, string $token): RedirectResponse
    {
        $path = $this->path($request, $token);
        if (! $path) {
            return to_route('import.show')->withErrors(['file' => 'That upload has expired. Please choose the file again.']);
        }

        try {
            $stats = ReadingImport::run($request->user(), $path);
        } catch (RuntimeException $exception) {
            return to_route('import.show')->withErrors(['file' => $exception->getMessage()]);
        } finally {
            Storage::disk('local')->deleteDirectory('imports/' . $request->user()->id);
        }

        $source = ReadingImport::SOURCES[$stats['source']] ?? 'your file';

        return to_route('users.show', $request->user()->username)
            ->with('status', "Imported {$stats['imported']} books from {$source}" . ($stats['skipped'] ? " ({$stats['skipped']} already on your shelf or skipped)" : '') . '. Your shelf just grew! 📚');
    }

    public function cancel(Request $request, string $token): RedirectResponse
    {
        Storage::disk('local')->deleteDirectory('imports/' . $request->user()->id);

        return to_route('import.show')->with('status', 'Import cancelled — nothing was changed.');
    }

    private function path(Request $request, string $token): ?string
    {
        if (! preg_match('/^[A-Za-z0-9]{32}$/', $token)) {
            return null;
        }
        $file = 'imports/' . $request->user()->id . '/' . $token . '.csv';

        return Storage::disk('local')->exists($file) ? Storage::disk('local')->path($file) : null;
    }
}
