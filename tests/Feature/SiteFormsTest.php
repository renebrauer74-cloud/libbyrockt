<?php

use App\Enums\Messages\MessageStatusEnum;
use App\Enums\Messages\MessageTypeEnum;
use App\Models\Message;

/**
 * Pflichtfelder des Kontaktformulars.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function personFormData(array $overrides = []): array
{
    return array_merge([
        'name' => 'Kim Beispiel',
        'email' => 'kim@example.com',
        'phone' => '0171 1234567',
        'privacy' => '1',
        'fax' => '',
    ], $overrides);
}

it('stores a contact message in the inbox', function () {
    $this->post(route('contact.store'), personFormData([
        'topic' => 'presse',
        'subject' => 'Interview-Anfrage',
        'body' => 'Wir würden gern über Libby berichten.',
    ]))->assertRedirect(route('contact').'#formular')->assertSessionHas('sent');

    expect(Message::query()->sole())
        ->type->toBe(MessageTypeEnum::Contact)
        ->status->toBe(MessageStatusEnum::New)
        ->name->toBe('Kim Beispiel')
        ->subject->toBe('Interview-Anfrage')
        ->details->toBe(['topic' => 'presse'])
        ->read_at->toBeNull();
});

it('uses the topic as subject when the contact form has none', function () {
    $this->post(route('contact.store'), personFormData(['topic' => 'booking', 'subject' => '', 'body' => 'Wir sind eine Band aus Essen.']));

    expect(Message::query()->sole()->subject)->toBe('Booking – Band / Künstler:in');
});

it('preselects the topic from the link', function () {
    $this->get(route('contact', ['thema' => 'booking']))
        ->assertOk()
        ->assertSee('value="booking" checked', false);

    $this->get(route('contact', ['thema' => 'gibtsnicht']))
        ->assertSee('value="allgemein" checked', false);
});

it('shows a thank-you note instead of the form after sending', function () {
    $this->withSession(['sent' => true])
        ->get(route('contact'))
        ->assertSee('Danke – Nachricht ist raus!')
        ->assertDontSee('name="body"', false);
});

it('validates the contact form and jumps back to it', function () {
    $this->from(route('contact'))
        ->post(route('contact.store'), personFormData(['email' => 'keine-mail', 'privacy' => '', 'topic' => 'gibtsnicht', 'body' => '']))
        ->assertRedirect(route('contact').'#formular')
        ->assertSessionHasErrors(['email', 'privacy', 'topic', 'body']);

    expect(Message::query()->count())->toBe(0);
});

it('rejects submissions that filled the spam trap', function () {
    $this->post(route('contact.store'), personFormData(['fax' => 'https://spam.example', 'topic' => 'allgemein', 'body' => 'Spam']))
        ->assertSessionHasErrors('fax');

    expect(Message::query()->count())->toBe(0);
});

it('rate limits the contact form', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('contact.store'), personFormData(['topic' => 'allgemein', 'body' => 'Hallo '.$attempt]));
    }

    $this->post(route('contact.store'), personFormData(['topic' => 'allgemein', 'body' => 'Zu viel']))
        ->assertTooManyRequests();

    expect(Message::query()->count())->toBe(5);
});
