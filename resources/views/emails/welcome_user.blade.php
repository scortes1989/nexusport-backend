<x-mail::message>
# ¡Hola {{ $user->name }}!

Gracias por registrarte en **NexusSport**. Tu cuenta ha sido creada exitosamente.

Ahora podrás realizar un seguimiento detallado de tus compras y gestionar tus datos de envío de forma rápida y sencilla.

<x-mail::button :url="config('app.url')">
Ir a NexusSport
</x-mail::button>

Saludos,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>
