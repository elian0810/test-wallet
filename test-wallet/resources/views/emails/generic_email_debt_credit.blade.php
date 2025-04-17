<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Pago</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
</head>

<body style="background-color: #F6F6F6; font-family: 'Roboto', sans-serif;">

    <div align="center" class="carta_informacion"
        style="background: white; width: 700px; padding: 20px; margin: 40px auto; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

        @if ($status)
            <h2 style="color: #2ecc71;">✅ Pago Aprobado</h2>
        @else
            <h2 style="color: #e74c3c;">❌ Pago Rechazado</h2>
        @endif

        <p style="font-size: 16px; color: #555; margin-top: 20px;">
            {{ $custom_message }}
        </p>

        <p style="font-size: 14px; color: #929292; margin-top: 40px;">Gracias por utilizar <strong>Test Wallet</strong>.
        </p>
    </div>

</body>

</html>
