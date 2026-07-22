<x-mail::message>
# Follow-ups need your attention

Hi {{ $name }},

You have {{ $followUps->count() }} {{ $followUps->count() === 1 ? 'follow-up' : 'follow-ups' }} due or overdue:

<x-mail::table>
| Follow-up | Linked to | Due |
| :-------- | :-------- | :-- |
@foreach ($followUps as $followUp)
| {{ $followUp->title }} ({{ $followUp->type->label() }}) | {{ $followUp->subject?->name ?? '—' }} | {{ optional($followUp->due_at)->format('d M Y') }} |
@endforeach
</x-mail::table>

<x-mail::button :url="route('staff.crm.follow-ups.index')">
Open follow-ups
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
