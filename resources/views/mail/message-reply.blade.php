Hallo {!! $inquiry->name !!},

{!! $reply->body !!}

Viele Grüße
{{ $reply->author?->name ? $reply->author->name.' – ' : '' }}{{ config('libby.org.name') }}
{{ config('libby.org.street') }}, {{ config('libby.org.zip') }} {{ config('libby.org.city') }}
{{ config('libby.org.phone') }} · {{ config('app.url') }}

----------------------------------------
Deine Nachricht vom {{ $inquiry->created_at->format('d.m.Y, H:i') }} Uhr:

{!! $inquiry->body !!}
