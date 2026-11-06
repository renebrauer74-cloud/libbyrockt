@if (config('libby.org.phone'))
<a class="footer-link" href="tel:{{ phone_link(config('libby.org.phone')) }}"><x-icon name="phone" /> {{ config('libby.org.phone') }}</a>
@endif
<a class="footer-link" href="mailto:{{ config('libby.org.email') }}"><x-icon name="mail" /> {{ config('libby.org.email') }}</a>
