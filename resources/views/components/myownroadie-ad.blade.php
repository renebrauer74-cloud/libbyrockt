{{-- Anzeige: Support-Bands über My Own Roadie (Schalter und Links in config/libby.php) --}}
@if (config('libby.myownroadie.enabled'))
<aside class="mor-ad" aria-label="Anzeige: My Own Roadie">
    <span class="mor-ad-label">Anzeige</span>
    <a class="mor-ad-brand" href="{{ config('libby.myownroadie.url') }}" target="_blank" rel="noopener">
        <img src="{{ asset('assets/img/myownroadie-icon.svg') }}" alt="" width="52" height="52">
        <span>my own<br>roadie</span>
    </a>
    <div class="mor-ad-copy">
        <strong>Band oder Künstler:in? Wir suchen Support-Bands!</strong>
        <span>Schau auf <a href="{{ config('libby.myownroadie.url') }}" target="_blank" rel="noopener">myownroadie.com</a> vorbei, registrier dich und finde deine nächsten Gigs.</span>
    </div>
    <a class="btn mor-ad-btn" href="{{ config('libby.myownroadie.register_url') }}" target="_blank" rel="noopener">Registrieren &amp; Gigs finden <x-icon name="external" /></a>
</aside>
@endif
