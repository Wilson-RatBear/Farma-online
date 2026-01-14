<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de Pedido</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1e88e5, #0d47a1); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
        .order-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #1e88e5; }
        .product-item { padding: 10px; border-bottom: 1px solid #eee; }
        .total { font-size: 1.2em; font-weight: bold; color: #0d47a1; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Confirmación de Pedido</h1>
            <p>Farmacia Online - Tu salud es nuestra prioridad</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $usuario->name }}</strong>,</p>
            <p>Tu pedido ha sido confirmado exitosamente. Aquí tienes los detalles:</p>
            
            <div class="order-info">
                <h3>📦 Información del Pedido</h3>
                <p><strong>Número de Orden:</strong> #{{ $pedido->numero_orden }}</p>
                <p><strong>Fecha:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Estado:</strong> <span style="color: #0d47a1; font-weight: bold;">Confirmado</span></p>
            </div>

            <div class="order-info">
                <h3>🛍️ Productos</h3>
                @foreach($pedido->items as $item)
                <div class="product-item">
                    <strong>{{ $item->producto->nombre }}</strong><br>
                    Cantidad: {{ $item->cantidad }} x ${{ number_format($item->precio_unitario, 2) }}
                </div>
                @endforeach
                <div class="total">
                    Total: ${{ number_format($pedido->total, 2) }}
                </div>
            </div>

            <div class="order-info">
                <h3>🚚 Información de Envío</h3>
                <p><strong>Dirección:</strong> {{ $pedido->direccion_envio }}</p>
                <p><strong>Ciudad:</strong> {{ $pedido->ciudad_envio }}</p>
                <p><strong>Teléfono:</strong> {{ $pedido->telefono_contacto }}</p>
                <p><strong>Método de Pago:</strong> {{ ucfirst($pedido->metodo_pago) }}</p>
            </div>

            <p>Gracias por confiar en Farmacia Online. Tu pedido será procesado y enviado pronto.</p>
        </div>
        
        <div class="footer">
            <p>Farmacia Online &copy; {{ date('Y') }} - Cuidando tu salud</p>
            <p>Si tienes alguna pregunta, contáctanos en nuestro chat de soporte.</p>
        </div>
    </div>
</body>
</html>