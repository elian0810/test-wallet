<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
</head>

<body style="background-color: #F6F6F6; font-family: 'Roboto', sans-serif;">

    <div align="center" class="carta_informacion"
        style="background: white; width: 700px; padding: 20px; margin: auto; border-radius: 5px;">
        <h3 style="color: #3C57E0;">Confirmación de Pago - Token de Verificación</h3>

        <p style="font-size: 15px; color: #929292;">Hola {{ $user_name}},</p>  

        <p style="font-size: 14px; color: #929292;">Hemos recibido tu solicitud para realizar un pago desde tu billetera. Para confirmar esta operación, por favor utiliza el siguiente token:</p>

        <h2 style="color: #3C57E0;">{{ $token }}</h2> 
        
        <p style="font-size: 14px; color: #929292;">Este código es válido por los próximos <strong>5 minutos</strong>. Si tú no solicitaste esta transacción, ignora este mensaje.</p>

        <p style="font-size: 14px; color: #929292;">Gracias por confiar en nosotros.</p>

        <p style="font-size: 14px; color: #929292;">El equipo de <strong>Test Wallet</strong></p>

    </div>

</body>

</html>
