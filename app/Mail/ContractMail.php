<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Str;
use Illuminate\Mail\Mailables\Address;
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
        return $this->from('contratgeneration@gmail.com', 'Contrat Generation')
            ->subject('Votre contrat de souscription')
            ->markdown('mail.contract_mail')
            ->with(['user' => $this->user])
            ->attach($this->contractPath, [
                'as' => 'contrat.docx',
                'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }
}
