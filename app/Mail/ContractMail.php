<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $contractPath;   
    public function __construct($user, $contractPath)
    {
        $this->user = $user;
        $this->contractPath = $contractPath;
    }

    public function build()
    {
            return $this->markdown('mail.contract-mail')
            ->subject('Votre contrat')
            ->attach($this->contractPath, [
                'as' => 'contrat.docx', // or .pdf
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contract Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.contract-mail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
