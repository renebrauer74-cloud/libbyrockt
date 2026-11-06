<?php

use App\Enums\Pages\PageEnum;
use App\Models\Page;

it('renders the page editors with the default texts', function (PageEnum $page, string $text) {
    actingAsAdmin()->get(route('admin.pages.edit', $page))
        ->assertOk()
        ->assertSee($text);
})->with([
    [PageEnum::Home, 'Laut wird’s nur mit dir'],
    [PageEnum::Contact, 'Schreib Libby'],
]);

it('saves the home page texts and shows them on the website', function () {
    actingAsAdmin()->put(route('admin.pages.update', PageEnum::Home), [
        'ticket_title' => 'Tickets, solange der Vorrat reicht',
        'ticket_text' => 'Die Shows sind schnell weg.',
        'booking_title' => 'Ihr seid eine Band?',
        'booking_text' => 'Meldet euch bei Libby.',
    ])->assertRedirect(route('admin.pages.edit', PageEnum::Home));

    expect(Page::query()->sole()->key)->toBe(PageEnum::Home);

    $this->get(route('home'))->assertSee(['Tickets, solange der Vorrat reicht', 'Die Shows sind schnell weg.', 'Ihr seid eine Band?', 'Meldet euch bei Libby.']);
});

it('saves the contact page texts and shows them on the website', function () {
    actingAsAdmin()->put(route('admin.pages.update', PageEnum::Contact), [
        'hero_title' => 'Ruf Libby an',
        'hero_lead' => 'Wir freuen uns auf dich.',
    ])->assertSessionHasNoErrors();

    $this->get(route('contact'))->assertSee(['Ruf Libby an', 'Wir freuen uns auf dich.']);
});

it('requires a headline', function () {
    actingAsAdmin()->put(route('admin.pages.update', PageEnum::Contact), ['hero_title' => ''])
        ->assertSessionHasErrors('hero_title');

    $this->put(route('admin.pages.update', PageEnum::Home), ['ticket_title' => '', 'booking_title' => 'Booking'])
        ->assertSessionHasErrors('ticket_title');
});

it('returns 404 for pages that do not exist', function () {
    actingAsAdmin()->get('/admin/seiten/jobs')->assertNotFound();
});
