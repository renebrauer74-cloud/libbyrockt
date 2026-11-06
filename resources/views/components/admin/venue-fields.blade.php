@props(['venue' => null, 'prefix' => '', 'cities' => []])
@php
    $listId = $prefix.'city-suggestions';
@endphp
<div class="grid-2">
    <div @class(['field', 'has-error' => $errors->has($prefix.'name')])>
        <label for="{{ $prefix }}name">Name *</label>
        <input id="{{ $prefix }}name" name="{{ $prefix }}name" type="text" maxlength="120" value="{{ old($prefix.'name', $venue?->name) }}" placeholder="z. B. Turbinenhalle 2">
        <x-admin.field-error :name="$prefix.'name'" />
    </div>
    <div @class(['field', 'has-error' => $errors->has($prefix.'city')])>
        <label for="{{ $prefix }}city">Stadt *</label>
        <input id="{{ $prefix }}city" name="{{ $prefix }}city" type="text" maxlength="80" value="{{ old($prefix.'city', $venue?->city) }}" placeholder="z. B. Oberhausen" list="{{ $listId }}">
        <datalist id="{{ $listId }}">
@foreach ($cities as $city)
            <option value="{{ $city }}">
@endforeach
        </datalist>
        <x-admin.field-error :name="$prefix.'city'" />
    </div>
</div>
<div class="grid-3">
    <div @class(['field', 'has-error' => $errors->has($prefix.'street')])>
        <label for="{{ $prefix }}street">Straße &amp; Nr.</label>
        <input id="{{ $prefix }}street" name="{{ $prefix }}street" type="text" maxlength="150" value="{{ old($prefix.'street', $venue?->street) }}">
        <x-admin.field-error :name="$prefix.'street'" />
    </div>
    <div @class(['field', 'has-error' => $errors->has($prefix.'zip')])>
        <label for="{{ $prefix }}zip">PLZ</label>
        <input id="{{ $prefix }}zip" name="{{ $prefix }}zip" type="text" maxlength="10" inputmode="numeric" value="{{ old($prefix.'zip', $venue?->zip) }}">
        <x-admin.field-error :name="$prefix.'zip'" />
    </div>
    <div @class(['field', 'has-error' => $errors->has($prefix.'website')])>
        <label for="{{ $prefix }}website">Website</label>
        <input id="{{ $prefix }}website" name="{{ $prefix }}website" type="url" maxlength="300" value="{{ old($prefix.'website', $venue?->website) }}" placeholder="https://…">
        <x-admin.field-error :name="$prefix.'website'" />
    </div>
</div>
