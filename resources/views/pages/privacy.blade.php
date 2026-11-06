<x-layouts.site title="Datenschutz">
    <section class="page-hero">
        <div class="container">
            <h1 class="page-title">Datenschutz</h1>
        </div>
    </section>
    <section class="section section-flush">
        <div class="container narrow prose">
            {{-- TODO: Vollständige Datenschutzerklärung einfügen (z. B. über einen Generator erstellen lassen). --}}
            <p class="notice"><x-icon name="alert" /> Die vollständige Datenschutzerklärung wird vor dem Livegang hier eingefügt.</p>
            <h2>Verantwortlicher</h2>
            <p>{{ config('libby.org.legal_name') }}, {{ config('libby.org.street') }}, {{ config('libby.org.zip') }} {{ config('libby.org.city') }}<br>
                E-Mail: <a href="mailto:{{ config('libby.org.email') }}">{{ config('libby.org.email') }}</a></p>
            <h2>Hinweise zu dieser Website</h2>
            <p>Diese Website lädt keine Schriftarten, Karten oder Tracking-Skripte von Drittanbietern. Schriften werden lokal ausgeliefert. Es wird nur ein technisch notwendiges Sitzungs-Cookie gesetzt (Schutz von Formularen vor Missbrauch, Login im Verwaltungsbereich).</p>
            <h2>Kontaktformular</h2>
            {{-- TODO: Speicherdauer und Rechtsgrundlagen vor dem Livegang juristisch prüfen lassen. --}}
            <p>Wenn du uns über das Kontaktformular schreibst, speichern wir deine Angaben (Name, E-Mail, optional Telefon, Betreff und Nachricht), um deine Anfrage zu bearbeiten und dir zu antworten. Die Daten sind nur für unser Team im geschützten Verwaltungsbereich einsehbar und werden nicht an Dritte weitergegeben. Rechtsgrundlage ist Art. 6 Abs. 1 lit. a und b DSGVO.</p>
            <h2>Externe Links</h2>
            <p>Links zu Ticketshops, Locations und Kartendiensten führen auf Seiten externer Anbieter; dort gelten deren Datenschutzbestimmungen. YouTube-Videos werden erst nach einem Klick geladen.</p>
        </div>
    </section>
</x-layouts.site>
