@props([
    'title' => null,
    'metaDescription' => 'Libby Rockt! Events & Konzerte aus Bochum: Konzerte, Tribute-Shows und Partys in Hallen und Clubs in ganz NRW. Alle Termine und Tickets.',
    'ogImage' => null,
])
@php
    $pageTitle = $title
        ? $title.' – '.config('app.name')
        : config('app.name').' – '.config('libby.org.tagline');
@endphp
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="theme-color" content="#ffffff">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ $ogImage ?? asset('assets/img/logo.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/apple-touch-icon.png') }}">
    <x-theme-script />
    <link rel="preload" href="{{ asset('assets/fonts/anton-latin.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ versioned_asset('assets/css/site.css') }}">
    <script src="{{ versioned_asset('assets/js/site.js') }}" defer></script>
</head>
<body>
    <a class="skip-link" href="#main">Zum Inhalt springen</a>
    <x-site.header />
    <main id="main">{{ $slot }}</main>
    <x-site.footer />
</body>
</html>
