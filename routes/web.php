<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarnabyController;
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
use App\Http\Controllers\ShelfEntryController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CoverController;
use App\Http\Controllers\QuoteWallController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\YearInReadingController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\ShelfStyleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\BuddyReadController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BookClubController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\SafetyController;
use App\Http\Controllers\ReportController;

Route::get('/', WelcomeController::class)->name('home');
Route::view('/offline', 'offline')->name('offline');
Route::get('/quotes/{quote:share_token}', [QuoteController::class, 'showPublic'])->name('quotes.public');
Route::get('/covers/{book}', [CoverController::class, 'show'])->name('covers.show');
// Public share pages (only for readers who turned sharing on)
Route::get('/u/{user:username}', [ShareController::class, 'tree'])->name('share.tree');
Route::get('/u/{user:username}/year/{year?}', [YearInReadingController::class, 'show'])->whereNumber('year')->name('year.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/push/subscriptions', [PushSubscriptionController::class, 'store'])->middleware('throttle:20,1')->name('push.subscribe');
    Route::delete('/push/subscriptions', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');
    Route::patch('/settings/privacy', [SafetyController::class, 'privacy'])->name('settings.privacy');
    Route::get('/follow-requests', [SafetyController::class, 'requests'])->name('follow-requests.index');
    Route::post('/follow-requests/{user:username}', [SafetyController::class, 'approve'])->name('follow-requests.approve');
    Route::delete('/follow-requests/{user:username}', [SafetyController::class, 'decline'])->name('follow-requests.decline');
    Route::post('/users/{user:username}/block', [SafetyController::class, 'block'])->name('users.block');
    Route::delete('/users/{user:username}/block', [SafetyController::class, 'unblock'])->name('users.unblock');
    Route::post('/reports', [ReportController::class, 'store'])->middleware('throttle:20,1')->name('reports.store');
    Route::get('/admin/reports', [ReportController::class, 'index'])->name('admin.reports');
    Route::post('/admin/reports/{report}', [ReportController::class, 'resolve'])->name('admin.reports.resolve');
    Route::patch('/settings/reminders', [SettingsController::class, 'reminders'])->name('settings.reminders');
    Route::get('/books', [BookController::class, 'index'])->middleware('throttle:search')->name('books.index');
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/books/{book}/shelf', [LibraryController::class, 'update'])->name('books.shelf.update');
    Route::post('/books/{book}/series', [\App\Http\Controllers\SeriesController::class, 'update'])->middleware('throttle:30,1')->name('books.series');
    Route::post('/books/{book}/log', [\App\Http\Controllers\ReadingLogController::class, 'store'])->name('books.log');
    Route::post('/shelves/{slot}/title', [LibraryController::class, 'updateShelfTitle'])->name('shelves.title.update');
    Route::patch('/shelf/{book}', [ShelfEntryController::class, 'update'])->name('shelf.entry.update');
    Route::patch('/shelf/{book}/move', [ShelfEntryController::class, 'move'])->name('shelf.entry.move');
    Route::delete('/shelves/{slot}/books/{book}', [LibraryController::class, 'removeBookFromShelf'])->name('shelves.books.remove');
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
    Route::get('/users/suggest', [UserController::class, 'suggest'])->middleware('throttle:120,1')->name('users.suggest');

    // Barnaby the worm (AI reading assistant)
    Route::post('/barnaby/chat', [BarnabyController::class, 'chat'])->name('barnaby.chat');
    Route::post('/barnaby/who', [BarnabyController::class, 'who'])->name('barnaby.who');
    Route::get('/barnaby/weekly', [BarnabyController::class, 'weekly'])->middleware('throttle:20,1')->name('barnaby.weekly');
    Route::post('/barnaby/explain', [BarnabyController::class, 'explain'])->name('barnaby.explain');
    Route::post('/barnaby/recap/{book}', [BarnabyController::class, 'recap'])->name('barnaby.recap');
    Route::post('/barnaby/buddy-reads/{buddyRead}/questions', [BarnabyController::class, 'discussion'])->name('barnaby.discussion');

    // Direct messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/contacts', [MessageController::class, 'contacts'])->name('messages.contacts');
    Route::get('/messages/list', [MessageController::class, 'list'])->middleware('throttle:120,1')->name('messages.list');
    Route::get('/messages/unread', [MessageController::class, 'unread'])->middleware('throttle:60,1')->name('messages.unread');
    Route::get('/messages/attachments/{attachment}', [MessageController::class, 'attachment'])->name('messages.attachment');
    Route::get('/messages/to/{user:username}', [MessageController::class, 'start'])->name('messages.start');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->whereNumber('conversation')->name('messages.show');
    Route::post('/messages/{conversation}', [MessageController::class, 'store'])->whereNumber('conversation')->middleware('throttle:60,1')->name('messages.store');
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])->whereNumber('conversation')->middleware('throttle:120,1')->name('messages.poll');
    Route::post('/messages/{conversation}/accept', [MessageController::class, 'accept'])->whereNumber('conversation')->name('messages.accept');
    Route::delete('/messages/{conversation}', [MessageController::class, 'destroy'])->whereNumber('conversation')->name('messages.destroy');
    Route::post('/posts/{post}/send', [MessageController::class, 'sendPost'])->middleware('throttle:30,1')->name('posts.send');

    // Posts, likes and replies
    Route::post('/posts', [PostController::class, 'store'])->middleware('throttle:posting')->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/react/{type}/{id}/like', [ReactionController::class, 'like'])->whereIn('type', ['post', 'review'])->whereNumber('id')->middleware('throttle:reactions')->name('reactions.like');
    Route::post('/react/{type}/{id}/comments', [ReactionController::class, 'comment'])->whereIn('type', ['post', 'review'])->whereNumber('id')->middleware('throttle:posting')->name('reactions.comment');
    Route::delete('/comments/{comment}', [ReactionController::class, 'destroyComment'])->name('comments.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    // Quote wall
    Route::get('/quotes', [QuoteWallController::class, 'index'])->name('quotes.index');

    // Import from Goodreads / StoryGraph
    Route::get('/import', [ImportController::class, 'show'])->name('import.show');
    Route::post('/import', [ImportController::class, 'store'])->middleware('throttle:import')->name('import.store');
    Route::get('/import/{token}', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/{token}', [ImportController::class, 'confirm'])->middleware('throttle:import')->name('import.confirm');
    Route::delete('/import/{token}', [ImportController::class, 'cancel'])->name('import.cancel');

    // Sharing
    Route::post('/share/toggle', [ShareController::class, 'toggle'])->name('share.toggle');
    Route::post('/shelf-style', ShelfStyleController::class)->name('shelf.style');
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::post('/decor-positions', [\App\Http\Controllers\DecorPositionController::class, 'store'])->middleware('throttle:60,1')->name('decor.positions');
    Route::delete('/decor-positions', [\App\Http\Controllers\DecorPositionController::class, 'destroy'])->name('decor.positions.reset');
    Route::post('/shop/streak-freeze', [ShopController::class, 'freeze'])->name('shop.freeze');
    Route::post('/shop/{item}', [ShopController::class, 'buy'])->whereAlpha('item')->name('shop.buy');
    Route::post('/shop/{item}/gift', [ShopController::class, 'gift'])->whereAlpha('item')->middleware('throttle:posting')->name('shop.gift');
    Route::post('/shop/{item}/toggle', [ShopController::class, 'toggle'])->whereAlpha('item')->name('shop.toggle');

    // Monthly reading challenges and badges
    Route::get('/challenges', [ChallengeController::class, 'index'])->name('challenges.index');

    // Book clubs
    Route::get('/clubs', [BookClubController::class, 'index'])->name('clubs.index');
    Route::post('/clubs', [BookClubController::class, 'store'])->middleware('throttle:posting')->name('clubs.store');
    Route::get('/clubs/{club}', [BookClubController::class, 'show'])->name('clubs.show');
    Route::patch('/clubs/{club}', [BookClubController::class, 'update'])->name('clubs.update');
    Route::post('/clubs/{club}/join', [BookClubController::class, 'join'])->name('clubs.join');
    Route::delete('/clubs/{club}/leave', [BookClubController::class, 'leave'])->name('clubs.leave');
    Route::post('/clubs/{club}/invite', [BookClubController::class, 'invite'])->middleware('throttle:posting')->name('clubs.invite');
    Route::post('/clubs/{club}/rounds', [BookClubController::class, 'startRound'])->name('clubs.rounds.store');
    Route::post('/clubs/{club}/rounds/finish', [BookClubController::class, 'finishRound'])->name('clubs.rounds.finish');
    Route::post('/clubs/{club}/sections/{section}/posts', [BookClubController::class, 'post'])->middleware('throttle:posting')->name('clubs.posts.store');
    Route::delete('/clubs/{club}/posts/{post}', [BookClubController::class, 'destroyPost'])->name('clubs.posts.destroy');
    Route::delete('/clubs/{club}/members/{member}', [BookClubController::class, 'removeMember'])->name('clubs.members.remove');
    Route::post('/barnaby/clubs/{club}/sections/{section}/questions', [BarnabyController::class, 'clubQuestions'])->name('barnaby.club');

    // Buddy reads
    Route::get('/buddy-reads', [BuddyReadController::class, 'index'])->name('buddy.index');
    Route::post('/buddy-reads', [BuddyReadController::class, 'store'])->middleware('throttle:posting')->name('buddy.store');
    Route::get('/buddy-reads/{buddyRead}', [BuddyReadController::class, 'show'])->name('buddy.show');
    Route::post('/buddy-reads/{buddyRead}/join', [BuddyReadController::class, 'join'])->name('buddy.join');
    Route::delete('/buddy-reads/{buddyRead}/leave', [BuddyReadController::class, 'leave'])->name('buddy.leave');
    Route::post('/buddy-reads/{buddyRead}/invite', [BuddyReadController::class, 'invite'])->middleware('throttle:posting')->name('buddy.invite');
    Route::post('/buddy-reads/{buddyRead}/comments', [BuddyReadController::class, 'comment'])->middleware('throttle:posting')->name('buddy.comment');
    Route::get('/users/{user:username}',[UserController::class,'show'])->name('users.show');
    Route::post('/users/{user:username}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user:username}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');
});

require __DIR__.'/auth.php';
