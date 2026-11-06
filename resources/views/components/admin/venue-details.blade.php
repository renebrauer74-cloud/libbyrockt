@props(['details' => []])
@php
    use App\Enums\Venues\VenueDetailEnum;

    $rows = array_values((array) old('details', $details ?? []));
@endphp
{{-- Datenblatt: Felder aus dem Menü oder eigene – alles optional, nur im Admin sichtbar --}}
<section class="venue-details" data-venue-details data-next-index="{{ count($rows) }}">
    <h2 class="panel-title">Datenblatt <span class="muted">· optional</span></h2>
    <p class="hint">Technik, Ansprechpartner &amp; Co. für die Planung – nur hier im Admin sichtbar, nicht auf der Website.</p>

    <div class="detail-rows" data-detail-rows>
@foreach ($rows as $index => $detail)
        <x-admin.venue-detail-row :index="$index" :detail="(array) $detail" />
@endforeach
    </div>
    <template data-detail-template>
        <x-admin.venue-detail-row index="__INDEX__" />
    </template>
    <p class="hint detail-empty" data-detail-empty @if ($rows !== []) hidden @endif>Noch keine Angaben – Feld aus dem Menü wählen:</p>

    <div class="detail-menu" role="group" aria-label="Feld hinzufügen">
@foreach (VenueDetailEnum::cases() as $preset)
        <button class="detail-chip" type="button" data-detail-preset data-label="{{ $preset->label() }}" data-placeholder="{{ $preset->placeholder() }}"><x-icon name="plus" /> {{ $preset->label() }}</button>
@endforeach
        <button class="detail-chip detail-chip-own" type="button" data-detail-add><x-icon name="plus" /> Eigenes Feld</button>
    </div>
    <x-admin.field-error name="details" />
</section>
