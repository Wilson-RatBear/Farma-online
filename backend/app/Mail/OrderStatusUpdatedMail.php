<?php

namespace App\Mail;

use App\Models\Pedido;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $nuevoEstado;
    public $estadoAnterior;

    /**
     * Create a new message instance.
     */
    public function __construct(Pedido $pedido, $nuevoEstado, $estadoAnterior = null)
    {
        $this->pedido = $pedido;
        $this->nuevoEstado = $nuevoEstado;
        $this->estadoAnterior = $estadoAnterior;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $estados = [
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado', 
            'enviado' => 'Enviado',
            'entregado' => 'Entregado',
            'cancelado' => 'Cancelado'
        ];

        $subject = "Actualización de tu pedido #{$this->pedido->numero_orden} - {$estados[$this->nuevoEstado]}";

        return $this->subject($subject)
                    ->view('emails.order-status-updated')
                    ->with([
                        'pedido' => $this->pedido,
                        'nuevoEstado' => $this->nuevoEstado,
                        'estadoAnterior' => $this->estadoAnterior,
                        'estados' => $estados
                    ]);
    }
}