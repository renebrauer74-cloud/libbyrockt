<?php

use App\Enums\MediaCollectionEnum;
use App\Enums\Messages\MessageStatusEnum;
use App\Mail\MessageReplyMail;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mailer\Exception\TransportException;

it('keeps the inbox behind the login', function () {
    $message = Message::factory()->create();

    $this->get(route('admin.messages.index'))->assertRedirect(route('admin.login'));
    $this->get(route('admin.messages.show', $message))->assertRedirect(route('admin.login'));
});

it('lists open messages and filters by status and search', function () {
    Message::factory()->create(['name' => 'Kontakt-Kim']);
    Message::factory()->booking()->create(['name' => 'Band-Ben']);
    Message::factory()->done()->create(['name' => 'Erledigt-Erika']);

    actingAsAdmin()->get(route('admin.messages.index'))
        ->assertOk()
        ->assertSee(['Kontakt-Kim', 'Band-Ben'])
        ->assertDontSee('Erledigt-Erika');

    $this->get(route('admin.messages.index', ['ansicht' => 'done']))->assertSee('Erledigt-Erika')->assertDontSee('Kontakt-Kim');
    $this->get(route('admin.messages.index', ['q' => 'band']))->assertSee('Band-Ben')->assertDontSee('Kontakt-Kim');
});

it('shows the unread count in the navigation', function () {
    Message::factory()->count(3)->create();
    Message::factory()->read()->create();

    actingAsAdmin()->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('<span class="nav-badge" title="3 ungelesen">3</span>', false);
});

it('shows a message with its details and marks it as read', function () {
    $message = Message::factory()->booking()->create(['subject' => 'Booking-Anfrage: Rusty Nails']);

    actingAsAdmin()->get(route('admin.messages.show', $message))
        ->assertOk()
        ->assertSee(['Booking-Anfrage: Rusty Nails', 'Booking – Band / Künstler:in']);

    expect($message->fresh()->read_at)->not->toBeNull();
});

it('marks a message as unread again', function () {
    $message = Message::factory()->read()->create();

    actingAsAdmin()->post(route('admin.messages.unread', $message))->assertRedirect(route('admin.messages.index'));

    expect($message->fresh()->read_at)->toBeNull();
});

it('updates status and internal note', function () {
    $message = Message::factory()->create();

    actingAsAdmin()
        ->from(route('admin.messages.show', $message))
        ->put(route('admin.messages.update', $message), ['status' => 'done', 'admin_note' => 'Jacke abgeholt'])
        ->assertRedirect(route('admin.messages.show', $message));

    expect($message->fresh())
        ->status->toBe(MessageStatusEnum::Done)
        ->admin_note->toBe('Jacke abgeholt');
});

it('sends a reply by mail and keeps it in the history', function () {
    Mail::fake();
    $message = Message::factory()->create(['email' => 'gast@example.com', 'subject' => 'Frage zum Einlass']);

    actingAsAdmin()
        ->post(route('admin.messages.replies.store', $message), ['body' => 'Einlass ist ab 22 Uhr.'])
        ->assertRedirect(route('admin.messages.show', $message).'#verlauf');

    Mail::assertSent(MessageReplyMail::class, fn (MessageReplyMail $mail): bool => $mail->hasTo('gast@example.com')
        && $mail->hasSubject('Re: Frage zum Einlass – Libby Rockt!'));

    expect($message->fresh())
        ->status->toBe(MessageStatusEnum::InProgress)
        ->replies->sole()->body->toBe('Einlass ist ab 22 Uhr.');

    $this->get(route('admin.messages.show', $message))->assertSee('Einlass ist ab 22 Uhr.');
});

it('renders the reply mail with greeting and original message', function () {
    $message = Message::factory()->create(['name' => 'Kim', 'body' => 'Wann ist Einlass?']);
    $reply = $message->replies()->make(['body' => 'Ab 22 Uhr.']);

    (new MessageReplyMail($message, $reply))
        ->assertSeeInText('Hallo Kim,')
        ->assertSeeInText('Ab 22 Uhr.')
        ->assertSeeInText('Wann ist Einlass?');
});

it('keeps the history unchanged when the reply mail fails', function () {
    Mail::shouldReceive('to')->andThrow(new TransportException('SMTP down'));
    $message = Message::factory()->create();

    actingAsAdmin()
        ->post(route('admin.messages.replies.store', $message), ['body' => 'Hallo!'])
        ->assertSessionHas('error');

    expect($message->replies()->count())->toBe(0);
});

it('downloads attachments only for admins and only of the own message', function () {
    Storage::fake('local');
    $message = Message::factory()->booking()->create();
    $message->addMedia(UploadedFile::fake()->create('lebenslauf.pdf', 50, 'application/pdf'))
        ->toMediaCollection(MediaCollectionEnum::MessageAttachments->value);
    $attachment = $message->getFirstMedia(MediaCollectionEnum::MessageAttachments->value);
    $other = Message::factory()->create();

    $this->get(route('admin.messages.attachment', [$message, $attachment->id]))->assertRedirect(route('admin.login'));

    actingAsAdmin()->get(route('admin.messages.attachment', [$message, $attachment->id]))
        ->assertOk()
        ->assertDownload('lebenslauf.pdf');

    $this->get(route('admin.messages.attachment', [$other, $attachment->id]))->assertNotFound();
});

it('deletes a message with replies and attachments', function () {
    Storage::fake('local');
    $message = Message::factory()->create();
    $message->addMedia(UploadedFile::fake()->image('foto.jpg'))->toMediaCollection(MediaCollectionEnum::MessageAttachments->value);
    $message->replies()->create(['body' => 'Gefunden!']);

    actingAsAdmin()->delete(route('admin.messages.destroy', $message))->assertRedirect(route('admin.messages.index'));

    expect(Message::query()->count())->toBe(0)
        ->and($message->media()->count())->toBe(0)
        ->and($message->replies()->count())->toBe(0);
});
