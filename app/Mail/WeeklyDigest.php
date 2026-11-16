<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

/** Sunday evening: what friends read this week, and your own week in books. */
class WeeklyDigest extends Mailable
{
    use Queueable;

    public function __construct(public User $user, public array $digest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '📚 Your week on Bookworm');
    }

    public function headers(): Headers
    {
        return new Headers(text: ['List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>', 'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.weekly', with: ['unsubscribeUrl' => $this->unsubscribeUrl()]);
    }

    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('email.unsubscribe', ['user' => $this->user->id]);
    }
}
