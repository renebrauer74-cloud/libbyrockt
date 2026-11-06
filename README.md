# Libby Rockt! – Website

Laravel-Website (Laravel 13, PHP 8.3+) mit Admin-Bereich für Veranstaltungen, Locations und Anfragen.
Basis ist das Matrix-Bochum-Projekt, angepasst an Libby: helles Design, Spielorte in mehreren Städten,
Kontakt- und Booking-Formular.

## Lokal

- PHP 8.5 aus Laravel Herd, MySQL-Datenbank `libbyrockt_laravel`
- Start: `php artisan serve --port=8095` → http://127.0.0.1:8095, Admin unter `/admin`

```bash
composer run setup                          # Abhängigkeiten, .env, App-Key, Migrationen
php artisan libby:admin admin@example.de    # Admin anlegen oder Passwort neu setzen
php artisan libby:demo                      # Beispiel-Locations und -Veranstaltungen anlegen
php artisan libby:demo --remove             # Beispiel-Veranstaltungen löschen
php artisan test --compact                  # Tests (Pest)
```

## Aufbau

```
app/
  Actions/Events/        Speichern, Duplizieren, Slug, Kalender, Google-Daten, Demo-Daten …
  Actions/Venues/        Locations speichern, Dubletten finden, Adresse/Route, Städte-Übersicht
  Actions/Messages/      Kontakt-Anfragen speichern, Antworten per E-Mail
  Actions/Pages/         bearbeitbare Seitentexte (Startseite, Kontakt, Social Media)
  Enums/                 Art, Ticket-Status, Status, Kontakt-Themen, Seiten, Media-Collections
  Http/Controllers/      öffentliche Seiten, Admin/ (Dashboard, Events, Locations, Veranstalter,
                         Anfragen, Seiten, Benutzer, Konto), Auth/
  Http/Requests/         Formular-Validierung
  Models/                Event, Venue, Organizer, Genre, Message, Page, User
config/libby.php         Firma, Kontakt, Ticket-Link, Social Media, Upload-Limits
resources/views/         Blade-Views, components/ (Layouts, Event-Karte, Formulare, Icons …)
public/assets/           CSS, JavaScript, Bilder, Schriften (ohne Build-Schritt)
public/uploads/          hochgeladene Bilder lokal (Spatie Media Library, Disk „uploads“)
```

Projektregeln für Actions, Models, Enums und Uploads stehen in `.ai/guidelines/` (über Laravel Boost in `AGENTS.md`).

## Veranstaltungen und Locations

- Jede Veranstaltung gehört zu einer **Location** (Name, Stadt, Adresse, Website). Neue Locations
  lassen sich direkt im Veranstaltungsformular anlegen; gleiche Name/Stadt-Kombination wird wiederverwendet.
- Öffentliche Seite **/locations** zeigt alle Spielorte mit kommenden Terminen, gruppiert nach Stadt.
- Programm-Filter: Art (`/programm/konzerte`), Stadt (`?stadt=`), Location (`?location=`), Genre (`?genre=`).
- Arten: **Konzert**, **Party**, **Sonstiges** – bei „Sonstiges“ ist „Was für eine Veranstaltung?“ Pflicht.
- **Genres**, **wiederkehrende Termine** (Serien), **Highlight-Zeitraum** fürs Startseiten-Karussell,
  **Veröffentlichung zu einem Zeitpunkt**, **Video** (YouTube oder eigene Datei), **externe Veranstalter**.
- Bilder werden als WebP in zwei Größen gespeichert (760 px und 1600 px Breite).
- Entwürfe sind nur für eingeloggte Admins sichtbar.
- Jedes Event hat eine Detailseite mit Routenplaner-Link, Kalender-Download (.ics) und strukturierte Daten für Google.

## Anfragen

Das Kontaktformular (`/kontakt`, Thema vorwählbar mit `?thema=booking`) landet im Admin unter **Anfragen**.
Von dort kann direkt per E-Mail geantwortet werden; Antwort-Adresse ist `LIBBY_EMAIL`.

## Livegang (Laravel Cloud)

1. Repository verbinden, MySQL-Datenbank und Bucket anlegen.
2. Umgebungsvariablen setzen (siehe `.env.example`, Block `LIBBY_*`), `MEDIA_DISK` auf den Bucket-Disk.
3. Nach dem ersten Deploy: `php artisan libby:admin <email> --password=…`.
4. Impressum und Datenschutzerklärung in `resources/views/pages/imprint.blade.php` / `privacy.blade.php` vervollständigen.
