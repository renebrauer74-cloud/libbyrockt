<?php

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

it('creates an admin with an existing bcrypt hash', function () {
    $hash = Hash::make('altes-passwort');

    $this->artisan('libby:admin', ['email' => 'Admin@Libbyrockt.de', '--name' => 'Admin', '--password-hash' => $hash])
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin@libbyrockt.de')->sole();
    expect(Hash::check('altes-passwort', $user->password))->toBeTrue();
});

it('creates an admin with a password option without prompting', function () {
    $this->artisan('libby:admin', ['email' => 'admin@libbyrockt.de', '--name' => 'Admin', '--password' => 'ein-langes-passwort'])
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin@libbyrockt.de')->sole();
    expect(Hash::check('ein-langes-passwort', $user->password))->toBeTrue();
});

it('rejects a password option that is too short', function () {
    $this->artisan('libby:admin', ['email' => 'admin@libbyrockt.de', '--password' => 'kurz'])
        ->assertFailed();

    expect(User::query()->count())->toBe(0);
});

it('rejects password hashes that are not bcrypt', function () {
    $this->artisan('libby:admin', ['email' => 'admin@libbyrockt.de', '--password-hash' => 'klartext'])
        ->assertFailed();
});

it('creates and removes the demo events', function () {
    Storage::fake('uploads');
    Event::factory()->create(['title' => 'Echtes Event']);

    $this->artisan('libby:demo')->assertSuccessful();
    expect(Event::query()->count())->toBe(10);

    $this->artisan('libby:demo', ['--remove' => true])->assertSuccessful();
    expect(Event::query()->pluck('title')->all())->toBe(['Echtes Event']);
});
