@php
    use App\Enums\Messages\ContactTopicEnum;

    $selectedTopic = old('topic', $topic->value);
@endphp
<x-layouts.site title="Kontakt" metaDescription="Kontakt zu Libby Rockt!: Booking-Anfragen, Kooperationen, Presse oder Fragen zu Tickets – schreib uns über das Kontaktformular.">
    <section class="page-hero">
        <div class="container">
            <p class="eyebrow">Kontakt</p>
            <h1 class="page-title">{{ $texts['hero_title'] }}</h1>
@if (filled($texts['hero_lead']))
            <p class="page-lead">{{ $texts['hero_lead'] }}</p>
@endif
        </div>
    </section>

    <section class="section section-flush">
        <div class="container form-layout">
            <div class="form-card" id="formular">
@if (session('sent'))
                <x-forms.sent title="Danke – Nachricht ist raus!" :again="route('contact')">Libby hat deine Nachricht apportiert. Wir melden uns so schnell wie möglich bei dir.</x-forms.sent>
@else
                <h2>Nachricht senden</h2>
                <p class="form-intro">Felder mit <span class="req">*</span> brauchen wir, um dir antworten zu können.</p>
                <x-forms.summary />
                <form class="form-grid" method="post" action="{{ route('contact.store') }}" novalidate data-site-form>
                    @csrf
                    <fieldset @class(['form-field', 'span-2', 'topic-field', 'has-error' => $errors->has('topic')])>
                        <legend>Worum geht’s? <span class="req" aria-hidden="true">*</span></legend>
                        <div class="topic-picker">
@foreach (ContactTopicEnum::cases() as $option)
                            <label><input type="radio" name="topic" value="{{ $option->value }}" @checked($selectedTopic === $option->value)><span>{{ $option->label() }}</span></label>
@endforeach
                        </div>
                        <x-forms.error name="topic" />
                    </fieldset>
                    <x-forms.person />
                    <x-forms.field name="subject" label="Betreff">
                        <input id="f-subject" name="subject" type="text" maxlength="200" value="{{ old('subject') }}" placeholder="z. B. Bandname oder Veranstaltung">
                    </x-forms.field>
                    <x-forms.field name="body" label="Deine Nachricht" required wide>
                        <textarea id="f-body" name="body" rows="7" maxlength="5000" required placeholder="Für Booking-Anfragen: Bandname, Genre, Links zu Musik/Videos, Wunschzeitraum …">{{ old('body') }}</textarea>
                    </x-forms.field>
                    <x-forms.consent />
                    <div class="form-actions span-2">
                        <span class="muted">Wir antworten in der Regel innerhalb weniger Tage.</span>
                        <button class="btn btn-accent btn-lg" type="submit"><x-icon name="send" /> Absenden</button>
                    </div>
                </form>
@endif
            </div>

            <aside class="form-aside">
                <div class="aside-card">
                    <h2>So erreichst du uns</h2>
                    <address class="aside-address">
                        {{ config('libby.org.legal_name') }}<br>
                        {{ config('libby.org.street') }}<br>
                        {{ config('libby.org.zip') }} {{ config('libby.org.city') }}
                    </address>
                    <x-site.contact-links />
                </div>
                <div class="aside-card">
                    <h2><x-icon name="ticket" /> Fragen zu Tickets?</h2>
                    <p>Bei Problemen mit einer Bestellung wende dich bitte zuerst an den jeweiligen Ticketanbieter – dort liegen deine Bestelldaten.</p>
                </div>
                <div class="aside-card">
                    <h2><x-icon name="venue" /> Locations</h2>
                    <p>Alle Spielorte mit Adresse und Routenplaner findest du auf der Locations-Seite.</p>
                    <a class="link-arrow" href="{{ route('venues.index') }}">Zu den Locations <x-icon name="arrow-right" /></a>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.site>
