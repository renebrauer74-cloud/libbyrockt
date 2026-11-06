@props(['index', 'detail' => []])
@php
    use App\Enums\Venues\VenueDetailEnum;

    $label = (string) ($detail['label'] ?? '');
    $placeholder = VenueDetailEnum::fromLabel($label)?->placeholder() ?? 'Angabe';
    $hasError = $errors->hasAny(["details.$index.label", "details.$index.value"]);
@endphp
<div @class(['detail-row', 'has-error' => $hasError]) data-detail-row>
    <input name="details[{{ $index }}][label]" type="text" maxlength="60" value="{{ $label }}" placeholder="Bezeichnung" aria-label="Bezeichnung" data-detail-label>
    <textarea name="details[{{ $index }}][value]" rows="2" maxlength="2000" placeholder="{{ $placeholder }}" aria-label="Angabe" data-detail-value>{{ $detail['value'] ?? '' }}</textarea>
    <button class="icon-btn icon-btn-danger" type="button" title="Feld entfernen" data-detail-remove><x-icon name="trash" /><span class="sr-only">Feld entfernen</span></button>
    <x-admin.field-error :name="'details.'.$index.'.label'" />
    <x-admin.field-error :name="'details.'.$index.'.value'" />
</div>
