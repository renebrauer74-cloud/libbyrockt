<?php

use App\Enums\Events\EventCategoryEnum;
use App\Enums\Events\TicketStatusEnum;
use App\Models\Event;
use App\Models\Venue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

it('renders every public page while the database is empty', function (string $routeName) {
    $this->get(route($routeName))->assertOk();
})->with(['home', 'events.index', 'venues.index', 'contact', 'imprint', 'privacy']);

it('renders every public page with demo data', function (string $routeName) {
    Storage::fake('uploads');
    Artisan::call('libby:demo');

    $this->get(route($routeName))->assertOk()->assertDontSee('Matrix Bochum');
})->with(['home', 'events.index', 'venues.index', 'contact']);

it('advertises my own roadie for support bands below the booking box', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Anzeige')
        ->assertSee('Wir suchen Support-Bands!')
        ->assertSee('href="https://myownroadie.com/registrieren" target="_blank" rel="noopener"', false);
});

it('hides the my own roadie ad when it is switched off', function () {
    config(['libby.myownroadie.enabled' => false]);

    $this->get(route('home'))->assertOk()->assertDontSee('myownroadie.com');
});

it('keeps the location data sheet off the public pages', function () {
    $venue = Venue::factory()->create(['name' => 'Turbinenhalle', 'details' => [['label' => 'Ansprechpartner:in', 'value' => 'Geheime Hausnummer 0815']]]);
    Event::factory()->for($venue)->create();

    $this->get(route('venues.index'))->assertOk()->assertSee($venue->name)->assertDontSee('Geheime Hausnummer 0815');
});

it('offers concerts and parties instead of the whole programme in the main navigation', function () {
    Event::factory()->category(EventCategoryEnum::Konzert)->create(['title' => 'Metal-Abend']);
    Event::factory()->category(EventCategoryEnum::Party)->create(['title' => 'Clubnacht']);

    $nav = fn (string $html): string => str($html)->between('<nav class="main-nav"', '</nav>')->toString();

    $home = $this->get(route('home'))->assertOk()->getContent();
    expect($nav($home))
        ->toContain('>Konzerte</a>', '>Partys</a>')
        ->not->toContain('>Programm</a>');

    $partys = $this->get(route('events.index', 'partys'))->assertOk()->assertSee('Clubnacht')->assertDontSee('Metal-Abend');
    expect($nav($partys->getContent()))
        ->toMatch('~href="'.preg_quote(route('events.index', 'partys'), '~').'"\s+aria-current="page"\s*>Partys</a>~');
});

it('offers a light and dark mode on the website and in the admin', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee('localStorage.getItem(key)', false)
        ->assertSee("key = 'libby-theme'", false);

    actingAsAdmin()->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('data-theme-toggle', false)
        ->assertSee("key = 'libby-theme-admin'", false)
        ->assertSee('Dunkles Design');
});

it('links the locations page in the main navigation and marks it active there', function () {
    $response = $this->get(route('venues.index'))->assertOk();

    expect($response->getContent())
        ->toMatch('~<a href="'.preg_quote(route('venues.index'), '~').'"\s+aria-current="page"\s*>Locations</a>~');
});

it('no longer serves the matrix pages', function (string $path) {
    $this->get($path)->assertNotFound();
})->with(['/jobs', '/firmen', '/fundsachen', '/anfahrt', '/location']);

it('loads styles, scripts and icons with a version so browsers fetch changed files', function () {
    $version = fn (string $path): string => $path.'?v='.filemtime(public_path($path));

    $this->get(route('home'))
        ->assertSee($version('assets/css/site.css'), false)
        ->assertSee($version('assets/js/site.js'), false)
        ->assertSee($version('assets/img/icons.svg').'#', false);

    actingAsAdmin()->get(route('admin.dashboard'))
        ->assertSee($version('assets/css/admin.css'), false)
        ->assertSee($version('assets/js/admin.js'), false);
});

it('renders the category pages of the programme', function (string $slug) {
    $this->get(route('events.index', $slug))->assertOk();
})->with(['konzerte', 'partys', 'sonstiges']);

it('rejects unknown programme categories', function () {
    $this->get('/programm/gibtsnicht')->assertNotFound();
});

it('redirects the old short urls to the programme', function () {
    $this->get('/konzerte')->assertRedirect('/programm/konzerte');
    $this->get('/partys')->assertRedirect('/programm/partys');
});

it('lists upcoming published events in chronological order', function () {
    Event::factory()->create(['title' => 'Später', 'starts_at' => now()->addDays(10)]);
    Event::factory()->create(['title' => 'Früher', 'starts_at' => now()->addDays(2)]);
    Event::factory()->draft()->create(['title' => 'Geheimer Entwurf']);
    Event::factory()->past()->create(['title' => 'Längst vorbei']);

    $this->get(route('events.index'))
        ->assertOk()
        ->assertSeeInOrder(['Früher', 'Später'])
        ->assertDontSee('Geheimer Entwurf')
        ->assertDontSee('Längst vorbei');
});

it('keeps an event without end time listed for six hours after it started', function () {
    Event::factory()->create(['title' => 'Läuft noch', 'starts_at' => now()->subHours(5), 'ends_at' => null]);
    Event::factory()->create(['title' => 'Ist vorbei', 'starts_at' => now()->subHours(7), 'ends_at' => null]);

    $this->get(route('events.index'))
        ->assertSee('Läuft noch')
        ->assertDontSee('Ist vorbei');
});

it('filters the programme by category, city and location', function () {
    $zeche = Venue::factory()->create(['name' => 'Zeche Bochum', 'city' => 'Bochum']);
    $turbinenhalle = Venue::factory()->create(['name' => 'Turbinenhalle', 'city' => 'Oberhausen']);
    Event::factory()->category(EventCategoryEnum::Konzert)->for($zeche)->create(['title' => 'Metal-Abend']);
    Event::factory()->category(EventCategoryEnum::Party)->for($turbinenhalle)->create(['title' => 'Clubnacht']);

    $this->get(route('events.index', 'konzerte'))
        ->assertSee('Metal-Abend')
        ->assertDontSee('Clubnacht');

    $this->get(route('events.index', ['stadt' => 'Oberhausen']))
        ->assertSee('Clubnacht')
        ->assertDontSee('Metal-Abend')
        ->assertSeeInOrder(['<option value="Bochum"', '<option value="Oberhausen" selected'], false);

    $this->get(route('events.index', ['location' => $zeche->slug]))
        ->assertSee(['Metal-Abend', 'Location · Bochum'])
        ->assertDontSee('Clubnacht');
});

it('ignores unknown cities and locations in the filter', function () {
    Event::factory()->create(['title' => 'Rocknacht']);

    $this->get(route('events.index', ['stadt' => 'Atlantis', 'location' => 'gibtsnicht']))
        ->assertOk()
        ->assertSee('Rocknacht');
});

it('shows city and location on the event cards and detail page', function () {
    $venue = Venue::factory()->create(['name' => 'Essigfabrik', 'city' => 'Köln', 'street' => 'Siegburger Straße 110', 'zip' => '50679']);
    $event = Event::factory()->for($venue)->create();

    $this->get(route('events.index'))->assertSee(['Köln', 'Essigfabrik']);

    $this->get(route('events.show', $event))
        ->assertSee(['Essigfabrik', 'Siegburger Straße 110, 50679 Köln'])
        ->assertSee('google.com/maps/dir/?api=1&amp;destination=Essigfabrik', false)
        ->assertSee('"addressLocality":"Köln"', false);
});

it('lists the locations with upcoming events grouped by city', function () {
    $zeche = Venue::factory()->create(['name' => 'Zeche Bochum', 'city' => 'Bochum']);
    $empty = Venue::factory()->create(['name' => 'Leere Halle', 'city' => 'Dortmund']);
    Event::factory()->count(2)->for($zeche)->create();
    Event::factory()->past()->for($empty)->create();

    $this->get(route('venues.index'))
        ->assertOk()
        ->assertSee(['Zeche Bochum', '2 Termine'])
        ->assertDontSee('Leere Halle')
        ->assertSee(route('events.index', ['location' => $zeche->slug]), false);
});

it('shows featured events first in the home slider', function () {
    Event::factory()->create(['title' => 'Normal und früh', 'starts_at' => now()->addDay()]);
    Event::factory()->featured()->create(['title' => 'Highlight und spät', 'starts_at' => now()->addWeeks(2)]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Highlight und spät', 'Normal und früh']);
});

it('shows the free label of other events instead of the category', function () {
    Event::factory()->category(EventCategoryEnum::Sonstiges)->create(['category_label' => 'Lesung']);

    $this->get(route('events.index'))->assertSee('Lesung');
});

it('shows an event with structured data and formatted description', function () {
    $event = Event::factory()->category(EventCategoryEnum::Konzert)->create([
        'title' => 'Dirty Honey',
        'description' => "Erster Absatz mit **fett**.\n\nMehr unter https://example.com",
        'price_presale' => '24 €',
        'price_box' => '29 €',
    ]);

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee('"@type":"MusicEvent"', false)
        ->assertSee('<strong>fett</strong>', false)
        ->assertSee('<a href="https://example.com" target="_blank" rel="noopener">', false)
        ->assertSee('VVK 24 € · AK 29 €');
});

it('escapes html in the event description', function () {
    $event = Event::factory()->create(['description' => '<script>alert(1)</script>']);

    $this->get(route('events.show', $event))
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('&lt;script&gt;', false);
});

it('offers tickets only while tickets are available', function () {
    $soldOut = Event::factory()->create(['ticket_status' => TicketStatusEnum::SoldOut, 'ticket_url' => 'https://tickets.example.com/x']);

    $this->get(route('events.show', $soldOut))
        ->assertSee('Ausverkauft')
        ->assertDontSee('Tickets kaufen');
});

it('marks almost sold out events and still sells tickets', function () {
    $event = Event::factory()->create(['ticket_status' => TicketStatusEnum::AlmostSoldOut, 'ticket_url' => 'https://tickets.example.com/x']);

    $this->get(route('events.show', $event))
        ->assertSee('<span class="ribbon ribbon-almostsoldout">Fast ausverkauft</span>', false)
        ->assertSee('Tickets kaufen');

    actingAsAdmin()->post(route('admin.events.store'), eventFormData(['ticket_status' => 'almostsoldout']))->assertSessionHasNoErrors();

    expect(Event::query()->latest('id')->first()->ticket_status)->toBe(TicketStatusEnum::AlmostSoldOut);
});

it('hides drafts from guests but shows them to admins', function () {
    $draft = Event::factory()->draft()->create();

    $this->get(route('events.show', $draft))->assertNotFound();

    actingAsAdmin()
        ->get(route('events.show', $draft))
        ->assertOk()
        ->assertSee('Entwurf – nur für eingeloggte Admins sichtbar.');
});

it('downloads a calendar entry for an event', function () {
    $event = Event::factory()->create([
        'title' => 'Neon Nights',
        'starts_at' => '2026-12-05 23:00:00',
        'ends_at' => null,
    ]);

    $response = $this->get(route('events.calendar', $event));

    $response->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
    expect($response->getContent())
        ->toContain('SUMMARY:Neon Nights')
        ->toContain('DTSTART:20261205T220000Z')
        ->toContain('DTEND:20261206T020000Z');
});

it('shows uploaded event images in two sizes', function () {
    Storage::fake('uploads');
    $event = Event::factory()->withImage()->create();

    $this->get(route('events.show', $event))
        ->assertSee('-md.webp 760w', false)
        ->assertSee('-lg.webp 1600w', false);
});
