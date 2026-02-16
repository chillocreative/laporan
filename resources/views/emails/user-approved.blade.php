<x-mail::message>
# Akaun Diluluskan

Salam {{ $user->name }},

Akaun anda telah diluluskan. Anda telah diberikan peranan **{{ $roleName }}**.

Anda kini boleh log masuk dan mula menggunakan sistem.

<x-mail::button :url="config('app.url')">
Log Masuk
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
