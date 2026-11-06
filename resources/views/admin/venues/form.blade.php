@php
    $isEdit = $venue !== null;
    $detachNote = $isEdit && $events->isNotEmpty()
        ? ' '.$events->count().' '.($events->count() === 1 ? 'Veranstaltung hat' : 'Veranstaltungen haben').' danach keine Location mehr.'
        : '';
@endphp
<x-layouts.admin :title="$isEdit ? 'Location: '.$venue->name : 'Neue Location'">
    <a class="back-link" href="{{ route('admin.venues.index') }}"><x-icon name="arrow-left" /> Alle Locations</a>

    <form class="panel narrow-panel" method="post" action="{{ $isEdit ? route('admin.venues.update', $venue) : route('admin.venues.store') }}">
        @csrf
@if ($isEdit)
        @method('PUT')
@endif
        <x-admin.venue-fields :venue="$venue" :cities="$cities" />
        <x-admin.venue-details :details="$venue?->details ?? []" />
        <div class="form-buttons">
            <button class="btn btn-accent" type="submit"><x-icon name="check" /> {{ $isEdit ? 'Änderungen speichern' : 'Location anlegen' }}</button>
            <a class="btn btn-ghost" href="{{ route('admin.venues.index') }}">Abbrechen</a>
        </div>
    </form>

@if ($isEdit)
    <section class="panel narrow-panel organizer-events">
        <h2 class="panel-title">Veranstaltungen ({{ $events->count() }})</h2>
@forelse ($events as $event)
        <a class="organizer-event" href="{{ route('admin.events.edit', $event) }}">
            <span>{{ $event->title }}</span>
            <span class="muted">{{ $event->starts_at->format('d.m.Y') }}@if ($event->isPast()) · vorbei @endif</span>
        </a>
@empty
        <p class="muted">Noch keine Veranstaltungen in dieser Location.</p>
@endforelse
    </section>

    <form class="danger-zone danger-zone-narrow" method="post" action="{{ route('admin.venues.destroy', $venue) }}"
          data-confirm="„{{ $venue->name }}“ wirklich löschen?{{ $detachNote }}">
        @csrf
        @method('DELETE')
        <div>
            <strong>Location löschen</strong>
            <p class="muted">Die Veranstaltungen bleiben erhalten, stehen danach aber ohne Location da.</p>
        </div>
        <button class="btn btn-danger" type="submit"><x-icon name="trash" /> Löschen</button>
    </form>
@endif
</x-layouts.admin>
