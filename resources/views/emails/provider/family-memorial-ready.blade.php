<x-mail::message>
# Your memorial page is ready

Hi {{ $name }},

**{{ $providerName }}** has created a memorial page for **{{ $personName }}** on BigTreeGarden.

You are the owner of this memorial and can manage it from your dashboard. If this is a new account, please use the password reset email we also sent to set your password.

<x-mail::button :url="$memorialUrl">
View memorial page
</x-mail::button>

Or open your [dashboard]({{ $dashboardUrl }}).

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
