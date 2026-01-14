<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Actualización de Estado de Pedido</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #1e88e5, #0d47a1); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border-radius: 0 0 10px 10px; }
        .order-info { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #1e88e5; }
        .status-update { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #2196f3; }
        .product-item { padding: 10px; border-bottom: 1px solid #eee; }
        .total { font-size: 1.2em; font-weight: bold; color: #0d47a1; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 0.9em; }
        .status-badge { 
            display: inline-block; 
            padding: 8px 16px; 
            background: #1e88e5; 
            color: white; 
            border-radius: 20px; 
            font-weight: bold; 
            margin: 5px 0; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 Actualización de Estado de Pedido</h1>
            <p>Farmacia Online - Tu salud es nuestra prioridad</p>
        </div>
        
        <div class="content">
            <p>Hola <strong>{{ $pedido->usuario->name }}</strong>,</p>
            <p>El estado de tu pedido ha sido actualizado:</p>
            
            <div class="status-update">
                <h3>📊 Cambio de Estado</h3>
                @if($estadoAnterior)
                    <p><strong>Estado anterior:</strong> {{ ucfirst($estadoAnterior) }}</p>
                @endif
                <p><strong>Nuevo estado:</strong></p>
                <div class="status-badge">{{ ucfirst($nuevoEstado) }}</div>
                
                @if($nuevoEstado == 'enviado')
                    <p style="margin-top: 10px;">📦 Tu pedido ha sido enviado y está en camino.</p>
                @elseif($nuevoEstado == 'entregado')
                    <p style="margin-top: 10px;">✅ Tu pedido ha sido entregado exitosamente.</p>
                @elseif($nuevoEstado == 'confirmado')
                    <p style="margin-top: 10px;">⏳ Tu pedido ha sido confirmado y está siendo preparado.</p>
                @endif
            </div>

            <div class="order-info">
                <h3>📦 Información del Pedido</h3>
                <p><strong>Número de Orden:</strong> #{{ $pedido->numero_orden }}</p>
                <p><strong>Fecha:</strong> {{ $pedido->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>Total:</strong> ${{ number_format($pedido->total, 2) }}</p>
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

            <p>Puedes ver el detalle completo de tu pedido en tu cuenta.</p>
            <p>Si tienes alguna pregunta, contáctanos en nuestro chat de soporte.</p>
        </div>
        
        <div class="footer">
            <p>Farmacia Online &copy; {{ date('Y') }} - Cuidando tu salud</p>
            <p>Este es un email automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>