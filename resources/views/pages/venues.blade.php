@inject('describeAddress', 'App\Actions\Venues\DescribeVenueAddressAction')
@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Str;

    $total = $cities->sum(fn ($venues): int => $venues->count());
@endphp
<x-layouts.site title="Locations" metaDescription="Alle Locations, in denen Libby Rockt! Konzerte und Partys veranstaltet – mit Adresse, Route und kommenden Terminen.">
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">Locations</p>
            <h1 class="page-title">Hier rockt Libby</h1>
            <p class="page-lead">
                {{ $total }} {{ $total === 1 ? 'Location' : 'Locations' }} in {{ $cities->count() }} {{ $cities->count() === 1 ? 'Stadt' : 'Städten' }} mit kommenden Shows –
                vom verschwitzten Clubkeller bis zur großen Halle.
            </p>
        </div>
    </section>

    <section class="section section-flush">
        <div class="container">
@if ($cities->isEmpty())
            <div class="empty-state">
                <x-icon name="pin" class="icon icon-xl" />
                <h3>Gerade keine Termine</h3>
                <p>Sobald neue Shows feststehen, findest du hier alle Locations.</p>
            </div>
@endif

@foreach ($cities as $city => $venues)
            <section class="venue-city" aria-labelledby="c-{{ Str::slug($city) }}">
                <h2 class="month-title" id="c-{{ Str::slug($city) }}">{{ caps($city) }} <span>{{ $venues->count() }}</span></h2>
                <div class="venues">
@foreach ($venues as $venue)
                    <article class="venue-card">
                        <div class="venue-card-head">
                            <div>
                                <h3>{{ caps($venue->name) }}</h3>
                                <address>{{ $describeAddress->execute($venue) }}</address>
                            </div>
                            <span class="count-pill">{{ $venue->upcoming_count }} {{ $venue->upcoming_count === 1 ? 'Termin' : 'Termine' }}</span>
                        </div>
                        <div class="venue-next"><x-icon name="calendar" /> <span>Nächste Show: <strong>{{ de_date_long(Carbon::parse($venue->next_starts_at)) }}</strong></span></div>
                        <div class="venue-links">
                            <a class="btn btn-sm btn-dark" href="{{ route('events.index', ['location' => $venue->slug]) }}"><x-icon name="ticket" /> Termine</a>
                            <a class="btn btn-sm" href="{{ $describeAddress->routeUrl($venue) }}" target="_blank" rel="noopener"><x-icon name="route" /> Route</a>
@if ($venue->website)
                            <a class="btn btn-sm btn-ghost" href="{{ $venue->website }}" target="_blank" rel="noopener"><x-icon name="globe" /> Website</a>
@endif
                        </div>
                    </article>
@endforeach
                </div>
            </section>
@endforeach
        </div>
    </section>

    <section class="section section-tight">
        <div class="container">
            <div class="visit-band">
                <div class="visit-item"><x-icon name="venue" /><div><strong>Du betreibst eine Location?</strong><span>Lass uns gemeinsam Shows auf die Bühne bringen.</span></div></div>
                <a class="btn btn-dark" href="{{ route('contact', ['thema' => 'location']) }}"><x-icon name="send" /> Kooperation anfragen</a>
            </div>
        </div>
    </section>
</x-layouts.site>
