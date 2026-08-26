<x-mail::message>
# Welcome to the team

Hi {{ $name }},

You have been added to **{{ $providerName }}** on BigTreeGarden as a **{{ $role }}**.

<x-mail::button :url="$portalUrl">
Open provider portal
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
