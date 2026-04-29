<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nuevo RSVP</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2 style="margin-bottom: 0.5rem;">Nuevo RSVP recibido</h2>

    <p>Evento: <strong>{{ $event->name }}</strong></p>

    <ul>
        <li>Invitado: <strong>{{ $guest->name }}</strong></li>
        <li>Codigo: <strong>{{ $guest->invitation_code }}</strong></li>
        <li>Estatus: <strong>{{ strtoupper($guest->rsvp_status) }}</strong></li>
        <li>Personas confirmadas: <strong>{{ (int) ($guest->guests_confirmed ?? 0) }}</strong></li>
    </ul>

    @if($guest->rsvp_message)
        <p>Mensaje: "{{ $guest->rsvp_message }}"</p>
    @endif

    @if($event->slug)
        <p>
            Ver evento:
            <a href="{{ url('/eventos/' . $event->slug) }}">{{ url('/eventos/' . $event->slug) }}</a>
        </p>
    @endif
</body>
</html>
