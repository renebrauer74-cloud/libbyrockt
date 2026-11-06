@inject('describeBadge', 'App\Actions\Events\DescribeEventBadgeAction')
@inject('loadPage', 'App\Actions\Pages\LoadPageAction')
@php
    use App\Enums\Events\EventCategoryEnum;
    use App\Enums\MediaCollectionEnum;
    use App\Enums\MediaConversionEnum;
    use App\Enums\Pages\PageEnum;

    $visibleEvents = config('libby.home.visible_events');
    $total = $slides->count();
    $texts = $loadPage->execute(PageEnum::Home)->content;
    $ticketsUrl = config('libby.org.tickets_url');
    $cities = $venues->groupBy('city');
@endphp
<x-layouts.site>
    {{-- HERO-KARUSSELL --}}
@if ($slides->isNotEmpty())
    <section class="hero" data-carousel data-interval="{{ config('libby.home.slide_seconds') }}" aria-roledescription="Karussell" aria-label="Aktuelle Konzerte">
        <h1 class="sr-only">Libby Rockt! – aktuelle Konzerte und Events</h1>
        <div class="hero-viewport" data-viewport>
            <div class="hero-track" data-track>
@foreach ($slides as $slide)
@php($background = $slide->getFirstMediaUrl(MediaCollectionEnum::Events->value, MediaConversionEnum::Medium->value))
                <div @class(['hero-slide', 'cat-'.$slide->category->value, 'is-active' => $loop->first]) role="group" aria-roledescription="Folie" aria-label="{{ $loop->iteration }} von {{ $total }}: {{ $slide->title }}" @unless ($loop->first) aria-hidden="true" @endunless>
                    <div class="hero-bg" aria-hidden="true">
@if ($background)
                        <img src="{{ $background }}" alt="" draggable="false">
@endif
                    </div>
                    <div class="container hero-inner">
                        <div class="hero-copy">
                            <p class="hero-kicker">
                                <span class="cat-badge">{{ caps($describeBadge->execute($slide)) }}</span>
                                {{ $slide->isFeaturedNow() ? 'Highlight' : 'Demnächst' }}
                            </p>
                            <h2 class="hero-title">{{ caps($slide->title) }}</h2>
@if ($slide->subtitle)
                            <p class="hero-sub">{{ $slide->subtitle }}</p>
@endif
                            <ul class="hero-meta">
                                <li><x-icon name="calendar" /> {{ de_date_long($slide->starts_at) }}</li>
                                <li><x-icon name="clock" /> {{ $slide->starts_at->format('H:i') }} Uhr</li>
@if ($slide->venue)
                                <li><x-icon name="pin" /> <span><strong>{{ $slide->venue->city }}</strong> · {{ $slide->venue->name }}</span></li>
@endif
                            </ul>
                            <div class="hero-actions">
@if ($slide->canBuyTickets())
                                <a class="btn btn-accent btn-lg" href="{{ $slide->ticket_url }}" target="_blank" rel="noopener" @unless ($loop->first) tabindex="-1" @endunless><x-icon name="ticket" /> Tickets sichern</a>
@endif
                                <a class="btn btn-outline btn-lg" href="{{ route('events.show', $slide) }}" @unless ($loop->first) tabindex="-1" @endunless>Mehr erfahren <x-icon name="arrow-right" /></a>
                            </div>
                        </div>
                        <a class="hero-poster" href="{{ route('events.show', $slide) }}" tabindex="-1" aria-hidden="true" draggable="false">
                            <x-events.media :event="$slide" sizes="(min-width: 900px) 440px, 90vw" :loading="$loop->first ? 'eager' : 'lazy'" />
@if ($slide->ticket_status->showsRibbon())
                            <span class="ribbon ribbon-{{ $slide->ticket_status->value }}">{{ $slide->ticket_status->label() }}</span>
@endif
                        </a>
                    </div>
                </div>
@endforeach
            </div>
        </div>

@if ($total > 1)
        <div class="hero-bar">
            <div class="container hero-bar-inner">
                <div class="hero-thumbs" role="group" aria-label="Veranstaltung wählen">
@foreach ($slides as $slide)
                    <button type="button" @class(['hero-thumb', 'cat-'.$slide->category->value, 'is-active' => $loop->first]) data-thumb="{{ $loop->index }}" aria-label="{{ $slide->title }}, {{ de_date_short($slide->starts_at) }}" @if ($loop->first) aria-current="true" @endif>
                        <span class="hero-thumb-bar" aria-hidden="true"><i></i></span>
                        <span class="hero-thumb-date">{{ $slide->starts_at->minDayName }} {{ $slide->starts_at->format('d.m.') }}{{ $slide->venue ? ' · '.$slide->venue->city : '' }}</span>
                        <span class="hero-thumb-title">{{ $slide->title }}</span>
                    </button>
@endforeach
                </div>
                <div class="hero-nav">
                    <span class="hero-counter" aria-hidden="true"><b data-current>01</b> / {{ sprintf('%02d', $total) }}</span>
                    <button type="button" class="hero-arrow" data-prev aria-label="Vorherige Veranstaltung"><x-icon name="arrow-left" /></button>
                    <button type="button" class="hero-arrow" data-next aria-label="Nächste Veranstaltung"><x-icon name="arrow-right" /></button>
                </div>
            </div>
        </div>
@endif
    </section>
@else
    <section class="hero hero-static">
        <div class="hero-slide is-active">
            <div class="hero-bg" aria-hidden="true"></div>
            <div class="container hero-inner hero-inner-single">
                <div class="hero-copy">
                    <p class="hero-kicker">Aus Bochum · für den ganzen Pott</p>
                    <h1 class="hero-title">Libby Rockt!</h1>
                    <p class="hero-sub">{{ config('libby.org.tagline') }} – Konzerte, Tribute-Shows und Partys in den besten Hallen und Clubs.</p>
                    <div class="hero-actions">
                        <a class="btn btn-accent btn-lg" href="{{ route('events.index') }}"><x-icon name="calendar" /> Zum Programm</a>
                        <a class="btn btn-outline btn-lg" href="{{ route('contact', ['thema' => 'booking']) }}"><x-icon name="mic" /> Booking</a>
                    </div>
                </div>
                <div class="hero-badge"><img src="{{ asset('assets/img/logo.png') }}" alt="Libby Rockt! Logo" width="683" height="800"></div>
            </div>
        </div>
    </section>
@endif

    {{-- DEMNÄCHST --}}
    <section class="section" id="programm">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Programm</p>
                    <h2 class="section-title">Demnächst mit Libby</h2>
                </div>
                <a class="link-arrow hide-sm" href="{{ route('events.index') }}">Gesamtes Programm <x-icon name="arrow-right" /></a>
            </div>

@if ($events->isNotEmpty())
            <div class="toolbar">
                <div class="chips" role="tablist" aria-label="Nach Art filtern" data-filter data-limit="{{ $visibleEvents }}">
                    <button type="button" class="chip is-active" data-filter-value="all" aria-selected="true">Alle <span>{{ $counts['all'] }}</span></button>
@foreach (EventCategoryEnum::cases() as $category)
@if ($counts[$category->value])
                    <button type="button" class="chip chip-{{ $category->value }}" data-filter-value="{{ $category->value }}" aria-selected="false">{{ $category->plural() }} <span>{{ $counts[$category->value] }}</span></button>
@endif
@endforeach
                </div>
                <x-events.view-toggle />
            </div>

            <div class="events" data-events data-view-target>
@foreach ($events as $event)
                <x-events.card :event="$event" :hidden="$loop->index >= $visibleEvents" />
@endforeach
            </div>
            <p class="empty-filter" data-empty hidden>In dieser Kategorie stehen gerade keine Termine an.</p>

            <div class="center-actions">
                <a class="btn btn-lg" href="{{ route('events.index') }}" data-more-link data-base="{{ route('events.index') }}">Gesamtes Programm ansehen <x-icon name="arrow-right" /></a>
            </div>
@else
            <div class="empty-state">
                <x-icon name="calendar" class="icon icon-xl" />
                <h3>Neue Termine folgen in Kürze</h3>
                <p>Libby plant schon die nächsten Shows – schau bald wieder vorbei!</p>
            </div>
@endif
        </div>
    </section>

    {{-- STÄDTE --}}
@if ($cities->isNotEmpty())
    <section class="section section-alt">
        <div class="container">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Auf Tour</p>
                    <h2 class="section-title">Hier ist Libby unterwegs</h2>
                </div>
                <a class="link-arrow hide-sm" href="{{ route('venues.index') }}">Alle Locations <x-icon name="arrow-right" /></a>
            </div>
            <div class="city-rail">
@foreach ($cities as $city => $cityVenues)
                <a class="city-tile" href="{{ route('events.index', ['stadt' => $city]) }}">
                    <span class="city-tile-count" title="Kommende Termine">{{ $cityVenues->sum('upcoming_count') }}</span>
                    <span class="city-tile-name">{{ caps($city) }}</span>
                    <span class="city-tile-venues">{{ $cityVenues->pluck('name')->implode(' · ') }}</span>
                    <x-icon name="arrow-right" />
                </a>
@endforeach
            </div>
        </div>
    </section>
@endif

    {{-- TICKETS --}}
    <section class="section section-tight">
        <div class="container">
            <div class="ticket-band">
                <div>
                    <p class="eyebrow">Tickets</p>
                    <h2 class="section-title">{{ $texts['ticket_title'] }}</h2>
@if (filled($texts['ticket_text']))
                    <p>{{ $texts['ticket_text'] }}</p>
@endif
                </div>
                <div class="ticket-band-actions">
                    <a class="btn btn-accent btn-lg" href="{{ route('events.index', 'konzerte') }}"><x-icon name="guitar" /> Alle Konzerte</a>
@if ($ticketsUrl)
                    <a class="btn btn-outline btn-lg" href="{{ $ticketsUrl }}" target="_blank" rel="noopener"><x-icon name="ticket" /> Ticketshop</a>
@else
                    <a class="btn btn-outline btn-lg" href="{{ route('venues.index') }}"><x-icon name="pin" /> Locations</a>
@endif
                </div>
            </div>
        </div>
    </section>

    {{-- BOOKING --}}
    <section class="section section-tight">
        <div class="container">
            <div class="visit-band">
                <div class="visit-item"><x-icon name="mic" /><div><strong>{{ $texts['booking_title'] }}</strong><span>{{ $texts['booking_text'] }}</span></div></div>
                <div class="visit-item"><x-icon name="mail" /><div><strong>Kontakt</strong><a href="mailto:{{ config('libby.org.email') }}">{{ config('libby.org.email') }}</a></div></div>
                <a class="btn btn-dark" href="{{ route('contact', ['thema' => 'booking']) }}"><x-icon name="send" /> Anfrage senden</a>
            </div>
            <x-myownroadie-ad />
        </div>
    </section>
</x-layouts.site>
