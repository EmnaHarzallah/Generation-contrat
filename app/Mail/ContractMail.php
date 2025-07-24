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
        return $this->markdown('mail.contrat_mail')
        ->subject('Votre contrat de souscription')
        ->attach($this->contractPath, [
            'as' => 'contrat.docx',
            'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ]);
    }

    public function envelope(): Envelope
{
    return new Envelope(
        from: new Address('contratgeneration@gmail.com', 'Contrat Generation'),
        subject: 'Contrat de souscription',
    );
}
    

public function attachments(): array
{
    $fileName = 'contrat_' . Str::slug($user->name) . '_' . time() . '.docx';
    $savePath = storage_path('app/public/contracts/' . $fileName);
    return [
        Attachment::fromPath($savePath)
            ->as($fileName)
            ->withMime('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
    ];
}
}
