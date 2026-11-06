@php
    $categoryPage = request()->routeIs('events.index') && request()->route('kategorie') !== null;
    $nav = [
        ['url' => route('events.index'), 'label' => 'Programm', 'active' => request()->routeIs('events.index') && ! $categoryPage],
        ['url' => route('events.index', 'konzerte'), 'label' => 'Konzerte', 'active' => request()->routeIs('events.index') && request()->route('kategorie') === 'konzerte'],
        ['url' => route('venues.index'), 'label' => 'Locations', 'active' => request()->routeIs('venues.index')],
        ['url' => route('contact'), 'label' => 'Kontakt', 'active' => request()->routeIs('contact')],
    ];
    $ticketsUrl = config('libby.org.tickets_url');
@endphp
<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="Libby Rockt! – Startseite">
            <img src="{{ asset('assets/img/logo-sm.png') }}" alt="" width="53" height="62">
            <span class="brand-text">Libby Rockt!<small>Events &amp; Konzerte</small></span>
        </a>

        <nav class="main-nav" id="main-nav" aria-label="Hauptnavigation">
            <ul>
@foreach ($nav as $item)
                <li><a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
@endforeach
            </ul>
            <a class="btn btn-accent nav-cta" href="{{ $ticketsUrl ?: route('events.index') }}" @if ($ticketsUrl) target="_blank" rel="noopener" @endif>
                <x-icon name="ticket" /> Tickets
            </a>
        </nav>

        <button class="nav-toggle" type="button" aria-controls="main-nav" aria-expanded="false" data-nav-toggle>
            <span class="sr-only">Menü</span>
            <span class="nav-toggle-bars" aria-hidden="true"><span></span><span></span><span></span></span>
        </button>
    </div>
</header>
