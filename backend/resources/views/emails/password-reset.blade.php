<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña - Farmacia Online</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            background: #f4f4f4; 
            padding: 20px; 
            margin: 0;
        }
        .container { 
            max-width: 600px; 
            background: white; 
            padding: 0;
            border-radius: 10px; 
            margin: 0 auto;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #1e88e5, #0d47a1); 
            color: white; 
            padding: 30px 20px; 
            text-align: center; 
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .button { 
            background: linear-gradient(135deg, #1e88e5, #0d47a1); 
            color: white; 
            padding: 14px 35px; 
            text-decoration: none; 
            border-radius: 8px; 
            display: inline-block; 
            margin: 25px 0; 
            font-weight: bold;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .footer { 
            margin-top: 30px; 
            padding-top: 20px; 
            border-top: 1px solid #eee; 
            color: #666;
            text-align: center;
        }
        .warning {
            background: #fff3e0;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #ff9800;
        }
        .product-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Restablecer Contraseña</h1>
        </div>
        
        <div class="content">
            <h2>Hola {{ $user->name }},</h2>
            <p>Has solicitado restablecer tu contraseña en <strong>Farmacia Online</strong>.</p>
            <p>Haz clic en el siguiente botón para crear una nueva contraseña:</p>
            
            <div style="text-align: center;">
                <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
            </div>
            
            <div class="warning">
                <p><strong>⚠️ Importante:</strong></p>
                <p>Si no solicitaste este cambio, puedes ignorar este email.</p>
                <p><strong>Este enlace expirará en 1 hora.</strong></p>
            </div>
            
            <p>¿No funciona el botón? Copia y pega este enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #1e88e5;">{{ $resetUrl }}</p>
        </div>
        
        <div class="footer">
            <p><strong>Saludos,<br>El equipo de Farmacia Online</strong></p>
            <p style="font-size: 12px; color: #999; margin-top: 10px;">
                Este es un email automático, por favor no respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>