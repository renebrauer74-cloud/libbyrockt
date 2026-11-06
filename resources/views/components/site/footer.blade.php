@inject('loadPage', 'App\Actions\Pages\LoadPageAction')
@php
    use App\Enums\Pages\PageEnum;
@endphp
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <img src="{{ asset('assets/img/logo-sm.png') }}" alt="Libby Rockt! Events &amp; Konzerte" width="111" height="130" loading="lazy">
            <p>Libby Rockt! holt Bands, Tribute-Shows und Partys in die Hallen und Clubs im Ruhrgebiet und darüber hinaus. Mit Herz, Haltung und ganz viel Lautstärke.</p>
            <x-site.social-links class="footer-social" :links="$loadPage->execute(PageEnum::Social)->content['social_links'] ?? []" />
        </div>
        <div>
            <h2 class="footer-title">Veranstalter</h2>
            <address>
                {{ config('libby.org.legal_name') }}<br>
                {{ config('libby.org.street') }}<br>
                {{ config('libby.org.zip') }} {{ config('libby.org.city') }}
            </address>
        </div>
        <div>
            <h2 class="footer-title">Kontakt</h2>
            <x-site.contact-links />
            <a class="footer-link" href="{{ route('contact', ['thema' => 'booking']) }}"><x-icon name="mic" /> Booking-Anfrage</a>
        </div>
        <div>
            <h2 class="footer-title">Entdecken</h2>
            <ul class="footer-nav">
                <li><a href="{{ route('events.index') }}">Programm</a></li>
                <li><a href="{{ route('events.index', 'konzerte') }}">Konzerte</a></li>
                <li><a href="{{ route('venues.index') }}">Locations</a></li>
                <li><a href="{{ route('contact') }}">Kontakt</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <span>© {{ now()->year }} {{ config('libby.org.legal_name') }}</span>
        <nav aria-label="Rechtliches">
            <a href="{{ route('imprint') }}">Impressum</a>
            <a href="{{ route('privacy') }}">Datenschutz</a>
        </nav>
    </div>
</footer>
