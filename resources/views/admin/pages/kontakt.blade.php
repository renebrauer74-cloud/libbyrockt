@php
    $content = $page->content;
    $hasError = fn (string ...$fields): string => $errors->hasAny($fields) ? ' has-error' : '';
@endphp
<x-layouts.admin title="Kontaktseite">
    <form class="page-form" method="post" action="{{ route('admin.pages.update', $pageKey) }}">
        @csrf
        @method('PUT')

        <section class="panel form-section">
            <h2 class="form-section-title"><span>1</span> Seitenkopf</h2>
            <div class="field{{ $hasError('hero_title') }}">
                <label for="hero_title">Überschrift *</label>
                <input id="hero_title" name="hero_title" type="text" maxlength="120" value="{{ old('hero_title', $content['hero_title']) }}">
                <x-admin.field-error name="hero_title" />
            </div>
            <div class="field{{ $hasError('hero_lead') }}">
                <label for="hero_lead">Einleitung</label>
                <textarea id="hero_lead" name="hero_lead" rows="3" maxlength="400">{{ old('hero_lead', $content['hero_lead']) }}</textarea>
                <x-admin.field-error name="hero_lead" />
            </div>
            <p class="hint">E-Mail-Adresse, Telefon und Anschrift kommen aus der Konfiguration (<code>LIBBY_EMAIL</code>, <code>LIBBY_PHONE</code>).</p>
        </section>

        <div class="page-save">
            <button class="btn btn-accent" type="submit"><x-icon name="check" /> Speichern</button>
            <a class="btn btn-ghost" href="{{ route('contact') }}" target="_blank" rel="noopener"><x-icon name="external" /> Kontaktseite ansehen</a>
        </div>
    </form>
</x-layouts.admin>
