<?php

namespace App\Mail;
use App\Models\PermohonanCuti;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StatusCutiNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $permohonan;

    /**
     * Create a new message instance.
     */
    public function __construct(PermohonanCuti $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Notifikasi Status Cuti Anda',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.status_cuti',
            with: [
                'permohonan' => $this->permohonan,
                // Tambahkan alasan penolakan secara eksplisit ke view
                'alasan_penolakan' => $this->permohonan->alasan_penolakan,
            ],
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
