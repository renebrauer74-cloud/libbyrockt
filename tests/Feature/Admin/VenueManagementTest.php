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

it('deletes a location and keeps its events', function () {
    $venue = Venue::factory()->create(['name' => 'Alte Halle']);
    $event = Event::factory()->for($venue)->create();

    actingAsAdmin()->delete(route('admin.venues.destroy', $venue))
        ->assertRedirect(route('admin.venues.index'))
        ->assertSessionHas('success', '„Alte Halle“ wurde gelöscht. 1 Veranstaltung hat jetzt keine Location mehr.');

    expect(Venue::query()->count())->toBe(0)
        ->and($event->fresh()->venue_id)->toBeNull();
});
