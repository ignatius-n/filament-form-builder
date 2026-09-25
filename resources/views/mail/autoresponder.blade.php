<x-mail::message>
{!! $body !!}

@if ($rows !== [])
<x-mail::table>
| {{ __('packstub-form-builder::form-builder.mail.field') }} | {{ __('packstub-form-builder::form-builder.mail.value') }} |
|:--|:--|
@foreach ($rows as $row)
| {{ $row['label'] }} | {{ str_replace(['|', "\n"], ['\|', ' '], $row['value']) }} |
@endforeach
</x-mail::table>
@endif
</x-mail::message>
