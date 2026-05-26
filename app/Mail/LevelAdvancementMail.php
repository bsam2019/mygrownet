<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailables\Content;

class LevelAdvancementMail extends BrandedMail
{
    public function __construct(
        public User $user,
        public string $newLevel,
        public array $benefits,
        public ?array $stats = null,
    ) {
        parent::__construct();
        $this->subject = "Congratulations! You've Advanced to {$newLevel}";
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.level-advancement',
            with: [
                'greeting' => "Congratulations, {$this->user->name}!",
                'newLevel' => $this->newLevel,
                'benefits' => $this->benefits,
                'stats' => $this->stats,
                'dashboardUrl' => config('app.url') . '/dashboard',
            ],
        );
    }
}
