<?php

namespace App\Mail;

use App\Enums\ServiceProviderRole;
use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderTeamAddedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceProvider $serviceProvider,
        public User $user,
        public ServiceProviderRole $role,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been added to '.$this->serviceProvider->name.' on BigTreeGarden',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.provider.team-added',
            with: [
                'name' => $this->user->name,
                'providerName' => $this->serviceProvider->name,
                'role' => $this->role->value,
                'portalUrl' => route('provider.dashboard'),
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
