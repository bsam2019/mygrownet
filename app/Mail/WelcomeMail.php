<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class WelcomeMail extends BrandedMail
{
    public function __construct(
        public User $user
    ) {
        parent::__construct();
        $this->subject('Welcome to MyGrowNet!');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
            with: [
                'greeting' => "Welcome, {$this->user->name}!",
                'actionUrl' => config('app.url') . '/dashboard',
                'actionText' => 'Go to Dashboard',
            ],
        );
    }
}
