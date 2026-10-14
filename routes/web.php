<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookCommentController;
use App\Http\Controllers\BookRatingController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/books', [BookController::class, 'index'])->name('books.index');
    Route::post('/books/rating', [BookRatingController::class, 'store'])->name('books.rate');
    Route::get('/books/{book}', [BookController::class, 'show'])->name('books.show');
    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/books/{book}/comments', [BookCommentController::class, 'store'])->name('books.comments.store');
    Route::delete('/books/{book}/comments/{comment}', [BookCommentController::class, 'destroy'])->name('books.comments.destroy');
    Route::post('/books/{book}/comments/{comment}/vote', [BookCommentController::class, 'vote'])->name('books.comments.vote');
});

require __DIR__.'/auth.php';
