<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Confirmacion RSVP</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin-bottom: 0.5rem;">Gracias por confirmar</h2>
    <p>Hola {{ $guest->name }},</p>
    <p>Registramos tu respuesta para el evento <strong>{{ $event->name }}</strong>.</p>

    <ul>
        <li>Estatus: <strong>{{ strtoupper($guest->rsvp_status) }}</strong></li>
        <li>Personas confirmadas: <strong>{{ (int) ($guest->guests_confirmed ?? 0) }}</strong></li>
    </ul>

    @if($event->slug)
        <p>
            Puedes revisar tu invitacion aqui:
            <a href="{{ url('/eventos/' . $event->slug . '?i=' . $guest->invitation_code) }}">
                {{ url('/eventos/' . $event->slug . '?i=' . $guest->invitation_code) }}
            </a>
        </p>
    @endif

    <p>Nos vemos pronto.</p>
</body>
</html>
