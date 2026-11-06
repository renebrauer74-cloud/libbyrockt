@php
    $content = $page->content;
    $hasError = fn (string ...$fields): string => $errors->hasAny($fields) ? ' has-error' : '';
@endphp
<x-layouts.admin title="Startseite">
    <form class="page-form" method="post" action="{{ route('admin.pages.update', $pageKey) }}">
        @csrf
        @method('PUT')

        <p class="hint section-hint">Das Karussell und „Demnächst mit Libby“ füllen sich automatisch aus den Veranstaltungen. Hier pflegst du die festen Texte darunter.</p>

        <section class="panel form-section">
            <h2 class="form-section-title"><span>1</span> Ticket-Band</h2>
            <div class="field{{ $hasError('ticket_title') }}">
                <label for="ticket_title">Überschrift *</label>
                <input id="ticket_title" name="ticket_title" type="text" maxlength="120" value="{{ old('ticket_title', $content['ticket_title']) }}">
                <x-admin.field-error name="ticket_title" />
            </div>
            <div class="field{{ $hasError('ticket_text') }}">
                <label for="ticket_text">Text</label>
                <textarea id="ticket_text" name="ticket_text" rows="3" maxlength="600">{{ old('ticket_text', $content['ticket_text']) }}</textarea>
                <x-admin.field-error name="ticket_text" />
            </div>
        </section>

        <section class="panel form-section">
            <h2 class="form-section-title"><span>2</span> Booking-Hinweis</h2>
            <div class="field{{ $hasError('booking_title') }}">
                <label for="booking_title">Überschrift *</label>
                <input id="booking_title" name="booking_title" type="text" maxlength="120" value="{{ old('booking_title', $content['booking_title']) }}">
                <x-admin.field-error name="booking_title" />
            </div>
            <div class="field{{ $hasError('booking_text') }}">
                <label for="booking_text">Text</label>
                <input id="booking_text" name="booking_text" type="text" maxlength="300" value="{{ old('booking_text', $content['booking_text']) }}">
                <x-admin.field-error name="booking_text" />
            </div>
        </section>

        <div class="page-save">
            <button class="btn btn-accent" type="submit"><x-icon name="check" /> Speichern</button>
            <a class="btn btn-ghost" href="{{ route('home') }}" target="_blank" rel="noopener"><x-icon name="external" /> Startseite ansehen</a>
        </div>
    </form>
</x-layouts.admin>
