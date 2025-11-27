<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Pedido;

class OrderConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $usuario;

    /**
     * Create a new message instance.
     */
    public function __construct(Pedido $pedido)
    {
        $this->pedido = $pedido;
        $this->usuario = $pedido->usuario;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✅ Confirmación de Pedido #' . $this->pedido->numero_orden,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.order-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}