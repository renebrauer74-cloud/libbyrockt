<x-layouts.admin title="Locations">
    <div class="list-toolbar">
        <p class="muted toolbar-note">Spielorte in allen Städten. Neue Locations entstehen auch direkt beim Anlegen einer Veranstaltung.</p>
        <div class="toolbar-actions">
            <a class="btn btn-accent" href="{{ route('admin.venues.create') }}"><x-icon name="plus" /> Neue Location</a>
        </div>
    </div>

@if ($venues->isEmpty())
    <div class="panel empty">
        <x-icon name="venue" class="icon icon-xl" />
        <p>Noch keine Locations angelegt.</p>
        <a class="btn btn-accent" href="{{ route('admin.venues.create') }}"><x-icon name="plus" /> Erste Location anlegen</a>
    </div>
@else
@inject('describeAddress', 'App\Actions\Venues\DescribeVenueAddressAction')
    <div class="position-list" role="list">
@foreach ($venues as $venue)
@php
    $address = $describeAddress->execute($venue);
    $confirm = '„'.$venue->name.'“ wirklich löschen?'
        .($venue->events_count > 0 ? ' '.$venue->events_count.' '.($venue->events_count === 1 ? 'Veranstaltung hat' : 'Veranstaltungen haben').' danach keine Location mehr.' : '');
@endphp
        <article class="prow orow" role="listitem">
            <div class="prow-main">
                <a class="prow-title" href="{{ route('admin.venues.edit', $venue) }}">{{ $venue->name }}</a>
                <span class="muted">{{ $address }}</span>
@if ($venue->website)
                <a class="muted link" href="{{ $venue->website }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://(www\.)?#i', '', rtrim($venue->website, '/')) }}</a>
@endif
            </div>
            <div class="prow-status">
                <span class="tag">{{ $venue->upcoming_count }} kommend</span>
                <span class="muted">{{ $venue->events_count }} gesamt</span>
            </div>
            <div class="erow-actions">
                <a class="icon-btn" href="{{ route('admin.venues.edit', $venue) }}" title="Bearbeiten"><x-icon name="edit" /><span class="sr-only">Bearbeiten</span></a>
                <form method="post" action="{{ route('admin.venues.destroy', $venue) }}" data-confirm="{{ $confirm }}">
                    @csrf
                    @method('DELETE')
                    <button class="icon-btn icon-btn-danger" type="submit" title="Löschen"><x-icon name="trash" /><span class="sr-only">Löschen</span></button>
                </form>
            </div>
        </article>
@endforeach
    </div>
@endif
</x-layouts.admin>
