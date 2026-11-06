<?php

namespace App\Mail;

use App\Models\Message;
use App\Models\MessageReply;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Antwort aus dem Admin-Posteingang an den Absender einer Nachricht.
 *
 * Die Nachricht heißt hier $inquiry, weil $message in Mail-Views für die Mail selbst reserviert ist.
 */
class MessageReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $inquiry, public MessageReply $reply) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('libby.org.email'), config('libby.org.name'))],
            subject: 'Re: '.($this->inquiry->subject ?: $this->inquiry->type->label()).' – '.config('libby.org.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(text: 'mail.message-reply');
    }
}
