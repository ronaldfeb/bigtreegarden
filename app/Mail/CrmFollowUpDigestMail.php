<?php

namespace App\Mail;

use App\Models\CrmFollowUp;
use App\Models\StaffUser;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class CrmFollowUpDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, CrmFollowUp>  $followUps
     */
    public function __construct(
        public StaffUser $staffUser,
        public Collection $followUps,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your BigTreeGarden CRM follow-ups',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.crm.follow-up-digest',
            with: [
                'name' => $this->staffUser->user?->name ?? 'there',
                'followUps' => $this->followUps,
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
