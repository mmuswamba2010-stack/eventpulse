<x-mail::message>
# Les événements à venir sur Event Pulse

{{ $intro }}

@foreach ($events as $event)
- **{{ $event->title }}** — {{ $event->event_date->translatedFormat('d/m/Y à H:i') }} · {{ $event->location }}
@endforeach

<x-mail::button :url="url(route('events.index'))">
Voir tous les événements
</x-mail::button>

Merci de votre confiance,<br>
{{ config('app.name') }}

<x-mail::subcopy>
Si vous ne souhaitez plus recevoir ces e-mails, [cliquez ici pour vous désinscrire]({{ $unsubscribeUrl }}).
</x-mail::subcopy>
</x-mail::message>
