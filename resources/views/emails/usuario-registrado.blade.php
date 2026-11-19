<div>
    <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
</div>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso al SGR VRIN</title>
</head>
<body style="margin:0;padding:24px;background:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#353535;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Tus datos de acceso al SGR VRIN</div>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:600px;margin:0 auto;border-collapse:collapse;background:#ffffff;">
        <tr>
            <td style="padding:18px 20px;background:#3765a0;color:#ffffff;text-align:center;font-size:16px;font-weight:bold;letter-spacing:.4px;border-radius:8px 8px 0 0;">
                SGR - UNAMBA
            </td>
        </tr>
        <tr>
            <td style="padding:44px 50px 40px;text-align:center;">
                <h1 style="margin:0 0 24px;font-size:24px;line-height:1.3;color:#333333;">¡Hola {{ $nombre }}!</h1>
                <p style="margin:0;font-size:16px;line-height:1.65;color:#555555;">
                    Te saludamos del <strong>SGR - UNAMBA</strong>. A continuación, compartimos tus datos de acceso para iniciar sesión:
                </p>

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:30px 0 40px;border-collapse:collapse;background:#eef4fb;border-radius:6px;text-align:left;">
                    <tr>
                        <td style="padding:24px 20px;font-size:15px;line-height:1.6;">
                            <strong style="color:#3765a0;">Correo electrónico:</strong><br>
                            <a href="mailto:{{ $correo }}" style="color:#3765a0;text-decoration:underline;overflow-wrap:anywhere;word-break:break-word;">{{ $correo }}</a>
                            <br><br>
                            <strong style="color:#3765a0;">Contraseña:</strong><br>
                            <span style="font-size:20px;font-weight:bold;letter-spacing:2px;color:#333333;">{{ $password }}</span>
                        </td>
                    </tr>
                </table>

                <a href="{{ $loginUrl }}" style="display:inline-block;padding:16px 30px;background:#3765a0;border-radius:30px;color:#ffffff;font-size:16px;font-weight:bold;text-decoration:none;">Iniciar sesión aquí</a>
                <p style="margin:32px 0 0;font-size:13px;line-height:1.5;color:#777777;">Si necesitas ayuda para ingresar, comunícate con el administrador del sistema.</p>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 20px;background:#a7a7a7;color:#ffffff;text-align:center;font-size:12px;border-radius:0 0 8px 8px;">
                Este es un mensaje automatizado generado por el sistema. Por favor, no respondas a este correo.
            </td>
        </tr>
    </table>
</body>
</html>
