<?php

namespace App\Mail;

use App\Models\PersonOfInterest;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyMemorialReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $familyUser,
        public PersonOfInterest $personOfInterest,
        public ServiceProvider $serviceProvider,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'A memorial page has been created for '.$this->personOfInterest->display_name,
        );
    }

    public function content(): Content
    {
        $memorialPage = $this->personOfInterest->memorialPages()->first();

        return new Content(
            markdown: 'emails.provider.family-memorial-ready',
            with: [
                'name' => $this->familyUser->name,
                'personName' => $this->personOfInterest->display_name,
                'providerName' => $this->serviceProvider->name,
                'memorialUrl' => $memorialPage !== null
                    ? route('memorial.public.show', $memorialPage->public_slug)
                    : route('dashboard'),
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }
}
