<?php

namespace App\Mail;

use App\Enums\ServiceProviderRole;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceProviderTeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceProviderInvitation $invitation,
        public ServiceProvider $serviceProvider,
        public string $plainToken,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You are invited to join '.$this->serviceProvider->name.' on BigTreeGarden',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.provider.team-invitation',
            with: [
                'providerName' => $this->serviceProvider->name,
                'role' => $this->invitation->role instanceof ServiceProviderRole
                    ? $this->invitation->role->value
                    : $this->invitation->role,
                'acceptUrl' => route('provider-invitations.show', $this->plainToken),
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
