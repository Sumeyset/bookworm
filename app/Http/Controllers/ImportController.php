<?php

namespace App\Http\Controllers;

use App\Support\ReadingImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class ImportController extends Controller
{
    public function show(): View
    {
        return view('import.show');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], ['file.mimes' => 'Please upload the .csv file from Goodreads or StoryGraph.']);

        try {
            $stats = ReadingImport::run($request->user(), $request->file('file')->getRealPath());
        } catch (RuntimeException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        $source = $stats['source'] === 'goodreads' ? 'Goodreads' : 'StoryGraph';

        return to_route('users.show', $request->user()->username)
            ->with('status', "Imported {$stats['imported']} books from {$source}" . ($stats['skipped'] ? " ({$stats['skipped']} already on your tree or skipped)" : '') . '. Your tree just grew! 🌳');
    }
}
