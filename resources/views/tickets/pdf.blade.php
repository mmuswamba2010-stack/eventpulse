<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Billet — {{ $ticket->event->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 12px; }
        .ticket { border: 2px solidrgb(235, 245, 92); border-radius: 12px; overflow: hidden; }
        .header { background:rgb(124, 162, 249); color: #ffffff; text-align: center; padding: 18px; }
        .header .brand { font-size: 10px; letter-spacing: 3px; text-transform: uppercase; opacity: 0.85; }
        .header h1 { font-size: 20px; margin-top: 6px; }
        .header p { margin-top: 6px; font-size: 11px; }
        .qr { text-align: center; padding: 20px; border-bottom: 1px dashed #9ca3af; }
        .qr img { width: 200px; height: 200px; }
        .code { font-family: DejaVu Sans Mono, monospace; font-size: 14px; font-weight: bold; letter-spacing: 2px; color: #111827; margin-top: 10px; }
        .code-hint { font-size: 9px; color: #6b7280; margin-top: 4px; }
        .details { padding: 18px 24px; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 5px 0; vertical-align: top; }
        .details td.label { color: #6b7280; width: 35%; }
        .footer { text-align: center; padding: 12px; background: #f3f4f6; color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <div class="brand">Event Pulse</div>
            <h1>{{ $ticket->event->title }}</h1>
            <p>
                {{ $ticket->event->event_date->format('d/m/Y à H:i') }} — {{ $ticket->event->location }}
            </p>
        </div>

        <div class="qr">
            <img src="data:image/svg+xml;base64,{{ $qrCode }}" alt="QR Code">
            <div class="code">{{ $ticket->formatted_number }}</div>
            <div class="code-hint">Référence billet</div>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td class="label">Accès</td>
                    <td><strong>{{ $ticket->accessLabel() }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Pass</td>
                    <td>{{ $ticket->ticketType?->name ?? 'Standard' }}</td>
                </tr>
                <tr>
                    <td class="label">Titulaire</td>
                    <td><strong>{{ $ticket->user->name }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Organisateur</td>
                    <td>{{ $ticket->event->user->name }}</td>
                </tr>
                <tr>
                    <td class="label">Prix</td>
                    <td>{{ $ticket->displayPrice() }}</td>
                </tr>
                <tr>
                    <td class="label">Paiement</td>
                    <td>{{ $ticket->paymentMethodLabel() }}</td>
                </tr>
                <tr>
                    <td class="label">Réf. billet</td>
                    <td style="font-family: DejaVu Sans Mono, monospace; letter-spacing: 1px;"><strong>{{ $ticket->formatted_number }}</strong></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Présentez ce QR Code à l'entrée de l'événement. Ce billet est personnel et ne peut être utilisé qu'une seule fois.
        </div>
    </div>
</body>
</html>
