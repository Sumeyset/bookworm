<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\BookPdfController;
use App\Http\Controllers\BookPdfHighlightController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\QuoteController;

Route::get('/', WelcomeController::class)->name('home');
Route::get('/quotes/{quote:share_token}', [QuoteController::class, 'showPublic'])->name('quotes.public');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/books/{book}/shelf', [LibraryController::class, 'update'])->name('books.shelf.update');
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');
    Route::get('/onboarding', [OnboardingController::class, 'show'])->name('onboarding.show');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
    Route::post('/pdfs/{bookPdf}/highlights/{highlight}/quote', [QuoteController::class, 'store'])->name('quotes.store');
    Route::post('/quotes/{quote}/share', [QuoteController::class, 'share'])->name('quotes.share');
    Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::post('/books/rating', [BookRatingController::class, 'store'])->name('books.rate');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::post('/books/{book}/pdfs', [BookPdfController::class, 'store'])->name('books.pdfs.store');
    Route::get('/books/{book}/pdfs/{bookPdf}', [BookPdfController::class, 'download'])->name('books.pdfs.download');
    Route::get('/books/{book}/pdfs/{bookPdf}/read', [BookPdfController::class, 'reader'])->name('books.pdfs.reader');
    Route::get('/books/{book}/pdfs/{bookPdf}/file', [BookPdfController::class, 'file'])->name('books.pdfs.file');
    Route::delete('/books/{book}/pdfs/{bookPdf}', [BookPdfController::class, 'destroy'])->name('books.pdfs.destroy');
    Route::get('/books/{book}/pdfs/{bookPdf}/highlights', [BookPdfHighlightController::class, 'index'])->name('books.pdfs.highlights.index');
    Route::post('/books/{book}/pdfs/{bookPdf}/highlights', [BookPdfHighlightController::class, 'store'])->name('books.pdfs.highlights.store');
    Route::delete('/books/{book}/pdfs/{bookPdf}/highlights/{highlight}', [BookPdfHighlightController::class, 'destroy'])->name('books.pdfs.highlights.destroy');
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user:username}',[UserController::class,'show'])->name('users.show');
    Route::post('/users/{user:username}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user:username}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');
});

require __DIR__.'/auth.php';
