<?php

use App\Models\Event;
use App\Models\Venue;

it('keeps the location management behind the login', function () {
    $this->get(route('admin.venues.index'))->assertRedirect(route('admin.login'));
});

it('lists locations by city with their event counts', function () {
    $zeche = Venue::factory()->create(['name' => 'Zeche Bochum', 'city' => 'Bochum']);
    Venue::factory()->create(['name' => 'Essigfabrik', 'city' => 'Köln']);
    Event::factory()->count(2)->for($zeche)->create();
    Event::factory()->past()->for($zeche)->create();

    actingAsAdmin()->get(route('admin.venues.index'))
        ->assertOk()
        ->assertSeeInOrder(['Zeche Bochum', 'Essigfabrik'])
        ->assertSee(['2 kommend', '3 gesamt']);
});

it('creates a location with a slug from name and city', function () {
    actingAsAdmin()->post(route('admin.venues.store'), [
        'name' => '  Turbinenhalle  ',
        'city' => 'Oberhausen',
        'street' => 'Im Lipperfeld 23',
        'zip' => '46047',
        'website' => 'www.turbinenhalle.de',
    ])->assertRedirect(route('admin.venues.index'))->assertSessionHas('success');

    expect(Venue::query()->sole())
        ->name->toBe('Turbinenhalle')
        ->slug->toBe('turbinenhalle-oberhausen')
        ->website->toBe('https://www.turbinenhalle.de');
});

it('accepts the website however it is typed', function (string $typed, string $stored) {
    actingAsAdmin()->post(route('admin.venues.store'), [
        'name' => 'Turbinenhalle',
        'city' => 'Oberhausen',
        'website' => $typed,
    ])->assertSessionHasNoErrors();

    expect(Venue::query()->sole()->website)->toBe($stored);
})->with([
    'ohne alles' => ['turbinenhalle.de', 'https://turbinenhalle.de'],
    'mit www' => ['www.turbinenhalle.de', 'https://www.turbinenhalle.de'],
    'mit http' => ['http://turbinenhalle.de', 'http://turbinenhalle.de'],
    'www vor http' => ['www.http://turbinenhalle.de', 'http://turbinenhalle.de'],
    'ohne Doppelpunkt' => ['https//www.turbinenhalle.de/', 'https://www.turbinenhalle.de/'],
    'groß und mit Leerzeichen' => [' HTTPS://www.turbinenhalle.de/programm ', 'https://www.turbinenhalle.de/programm'],
    'doppelt' => ['https://http://turbinenhalle.de', 'http://turbinenhalle.de'],
    'Domain mit http am Anfang' => ['httpbin.org', 'https://httpbin.org'],
]);

it('does not repeat the city in the slug when the name contains it', function () {
    actingAsAdmin()->post(route('admin.venues.store'), ['name' => 'Zeche Bochum', 'city' => 'Bochum']);

    expect(Venue::query()->sole()->slug)->toBe('zeche-bochum');
});

it('rejects duplicates, invalid postcodes and links without a domain', function () {
    Venue::factory()->create(['name' => 'Zeche Bochum', 'city' => 'Bochum']);

    actingAsAdmin()->post(route('admin.venues.store'), [
        'name' => 'ZECHE BOCHUM',
        'city' => 'bochum',
    ])->assertSessionHasErrors(['name' => 'Diese Location gibt es in Bochum schon.']);

    $this->post(route('admin.venues.store'), [
        'name' => 'Neue Halle',
        'city' => 'Essen',
        'zip' => '!!',
        'website' => 'kein-link',
    ])->assertSessionHasErrors(['zip', 'website']);

    expect(Venue::query()->count())->toBe(1);
});

it('updates a location and keeps its own name when checking duplicates', function () {
    $venue = Venue::factory()->create(['name' => 'Matrix', 'city' => 'Bochum']);

    actingAsAdmin()->put(route('admin.venues.update', $venue), [
        'name' => 'Matrix',
        'city' => 'Bochum',
        'street' => 'Hauptstraße 200',
    ])->assertSessionHasNoErrors();

    expect($venue->fresh()->street)->toBe('Hauptstraße 200');
});

it('saves a self-built data sheet and drops empty rows', function () {
    actingAsAdmin()->post(route('admin.venues.store'), [
        'name' => 'Turbinenhalle',
        'city' => 'Oberhausen',
        'details' => [
            ['label' => ' Bühnengröße ', 'value' => ' 10 × 8 m '],
            ['label' => 'Strom', 'value' => ''],
            ['label' => 'Mein eigenes Feld', 'value' => "Zeile 1\nZeile 2"],
        ],
    ])->assertSessionHasNoErrors();

    expect(Venue::query()->sole()->details)->toBe([
        ['label' => 'Bühnengröße', 'value' => '10 × 8 m'],
        ['label' => 'Mein eigenes Feld', 'value' => "Zeile 1\nZeile 2"],
    ]);
});

it('needs a label when a data sheet value is filled in', function () {
    actingAsAdmin()->post(route('admin.venues.store'), [
        'name' => 'Turbinenhalle',
        'city' => 'Oberhausen',
        'details' => [['label' => '', 'value' => '63 A CEE']],
    ])->assertSessionHasErrors('details.0.label');

    expect(Venue::query()->count())->toBe(0);
});

it('clears the data sheet when all rows are removed', function () {
    $venue = Venue::factory()->create([
        'name' => 'Matrix',
        'city' => 'Bochum',
        'details' => [['label' => 'Kapazität', 'value' => '800']],
    ]);

    actingAsAdmin()->put(route('admin.venues.update', $venue), ['name' => 'Matrix', 'city' => 'Bochum'])
        ->assertSessionHasNoErrors();

    expect($venue->fresh()->details)->toBeNull();
});

it('shows the data sheet with its menu in the admin only', function () {
    $venue = Venue::factory()->create([
        'name' => 'Matrix',
        'city' => 'Bochum',
        'details' => [['label' => 'Bühnengröße', 'value' => '8 × 6 m']],
    ]);

    actingAsAdmin()->get(route('admin.venues.edit', $venue))
        ->assertOk()
        ->assertSee('Datenblatt')
        ->assertSee('value="Bühnengröße"', false)
        ->assertSee('8 × 6 m')
        ->assertSee('Eigenes Feld')
        ->assertSee('data-label="Backline"', false);

    $this->get(route('admin.venues.index'))->assertSee('1 Angabe');
});

it('deletes a location and keeps its events', function () {
    $venue = Venue::factory()->create(['name' => 'Alte Halle']);
    $event = Event::factory()->for($venue)->create();

    actingAsAdmin()->delete(route('admin.venues.destroy', $venue))
        ->assertRedirect(route('admin.venues.index'))
        ->assertSessionHas('success', '„Alte Halle“ wurde gelöscht. 1 Veranstaltung hat jetzt keine Location mehr.');

    expect(Venue::query()->count())->toBe(0)
        ->and($event->fresh()->venue_id)->toBeNull();
});
