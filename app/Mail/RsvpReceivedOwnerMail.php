<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Guest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class RsvpReceivedOwnerMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Event $event, public Guest $guest)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuevo RSVP recibido - ' . $this->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rsvp.owner-notification',
            with: [
                'event' => $this->event,
                'guest' => $this->guest,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
