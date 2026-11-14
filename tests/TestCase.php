<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests must never touch real uploads (photos, PDFs, message files): test users
        // get ids like 1, 2, 3 — the same as real accounts — so anything a test
        // stores or deletes on the public disk goes to a throwaway fake instead.
        Storage::fake('public');
        Storage::fake('local');
    }
}
