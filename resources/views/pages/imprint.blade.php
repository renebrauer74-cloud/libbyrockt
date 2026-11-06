<x-layouts.site title="Impressum">
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Impressum</h1>
        </div>
    </section>
    <section class="section section-flush">
        <div class="container narrow prose">
            {{-- Angaben aus dem Handelsregister übernommen – bitte vor dem Livegang prüfen und vervollständigen (config/libby.php bzw. .env). --}}
            <h2>Angaben gemäß § 5 DDG</h2>
            <p>{{ config('libby.org.legal_name') }}<br>
                {{ config('libby.org.street') }}<br>
                {{ config('libby.org.zip') }} {{ config('libby.org.city') }}</p>
@if (config('libby.org.managing_director'))
            <p>Vertreten durch den Geschäftsführer: {{ config('libby.org.managing_director') }}</p>
@endif
            <h2>Kontakt</h2>
            <p>@if (config('libby.org.phone'))Telefon: {{ config('libby.org.phone') }}<br>@endif
                E-Mail: <a href="mailto:{{ config('libby.org.email') }}">{{ config('libby.org.email') }}</a></p>
            <h2>Registereintrag</h2>
            <p>{{ config('libby.org.register') ?: 'wird nachgereicht' }}</p>
            <h2>Umsatzsteuer-ID</h2>
            <p>Umsatzsteuer-Identifikationsnummer gemäß § 27a UStG: {{ config('libby.org.vat_id') ?: 'wird nachgereicht' }}</p>
            <h2>Verbraucherstreitbeilegung</h2>
            <p>Wir sind nicht bereit oder verpflichtet, an Streitbeilegungsverfahren vor einer Verbraucherschlichtungsstelle teilzunehmen.</p>
        </div>
    </section>
</x-layouts.site>
