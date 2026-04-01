<x-mail::message>
# Kata Laluan Sementara

Salam {{ $user->name }},

Kami telah menerima permintaan untuk menetapkan semula kata laluan anda. Berikut adalah kata laluan sementara anda:

<x-mail::panel>
<strong style="font-size: 18px; letter-spacing: 2px;">{{ $temporaryPassword }}</strong>
</x-mail::panel>

Sila gunakan kata laluan ini untuk log masuk. Anda akan diminta untuk menukar kata laluan selepas log masuk.

<x-mail::button :url="config('app.url') . '/login'">
Log Masuk
</x-mail::button>

**Penting:** Jika anda tidak membuat permintaan ini, sila abaikan e-mel ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
