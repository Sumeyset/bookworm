<?php

/*
 * Push notifications to phones and browsers (Web Push).
 * Make the keys once with:  php artisan webpush:keys
 * and put them in .env. Without them, push is simply off.
 */
return [
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    // Who to contact about the pushes: a mailto: address or the site's URL.
    'subject' => env('VAPID_SUBJECT', env('APP_URL', 'http://localhost')),
];
