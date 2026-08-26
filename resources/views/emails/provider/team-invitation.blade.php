<x-mail::message>
# You're invited

You have been invited to join **{{ $providerName }}** on BigTreeGarden as a **{{ $role }}**.

<x-mail::button :url="$acceptUrl">
Accept invitation
</x-mail::button>

This invitation expires in 7 days.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
