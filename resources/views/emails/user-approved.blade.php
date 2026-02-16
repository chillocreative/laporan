<x-mail::message>
# Account Approved

Hello {{ $user->name }},

Your account has been approved. You have been assigned the role of **{{ $roleName }}**.

You can now log in and start using the system.

<x-mail::button :url="config('app.url')">
Log In
</x-mail::button>

Thank you,<br>
{{ config('app.name') }}
</x-mail::message>
