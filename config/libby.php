<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Veranstalter
    |--------------------------------------------------------------------------
    |
    | Firmendaten und Kontakt – erscheinen in Header, Footer, Kontaktseite,
    | Impressum, Event-Detailseiten (Veranstalter) und strukturierten Daten.
    | Die Spielorte selbst werden im Admin-Bereich unter „Locations“ gepflegt.
    |
    */

    'org' => [
        'name' => 'Libby Rockt!',
        'legal_name' => 'Libby Rockt! Events & Konzerte GmbH',
        'tagline' => 'Events & Konzerte aus dem Ruhrpott',
        'street' => 'Hauptstraße 200',
        'zip' => '44892',
        'city' => 'Bochum',
        'phone' => env('LIBBY_PHONE'),
        'email' => env('LIBBY_EMAIL', 'info@libbyrockt.de'),
        'managing_director' => 'Dirk Zimmer',
        'register' => 'Amtsgericht Bochum, HRB 12739',
        'vat_id' => env('LIBBY_VAT_ID'),

        // Allgemeiner Ticketshop (z. B. Eventim, Reservix) – leer: der Tickets-Button führt ins Programm
        'tickets_url' => env('LIBBY_TICKETS_URL'),

        'instagram' => env('LIBBY_INSTAGRAM_URL'),
        'facebook' => env('LIBBY_FACEBOOK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Uploads
    |--------------------------------------------------------------------------
    */

    'uploads' => [
        'max_kilobytes' => 12 * 1024,

        // Eigene Event-Videos und Videos auf gepflegten Seiten (MP4, WebM, MOV)
        'video_max_kilobytes' => 100 * 1024,

        // Anhänge aus dem Kontaktformular (z. B. Pressekit einer Band)
        'attachment_max_kilobytes' => 10 * 1024,

        // Anhänge sind nicht öffentlich – auf Laravel Cloud einen privaten Bucket als Disk eintragen
        'private_disk' => env('MEDIA_PRIVATE_DISK', 'local'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Posteingang
    |--------------------------------------------------------------------------
    |
    | Nachrichten pro Seite im Admin-Posteingang.
    |
    */

    'inbox' => [
        'per_page' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Startseite
    |--------------------------------------------------------------------------
    */

    'home' => [
        'slides' => 6,
        'events' => 24,
        'visible_events' => 9,

        // Sekunden, bis das Karussell zur nächsten Veranstaltung weiterspringt
        'slide_seconds' => 6,
    ],

];
