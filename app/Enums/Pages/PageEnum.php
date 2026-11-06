<?php

namespace App\Enums\Pages;

use App\Enums\Social\SocialPlatformEnum;

/**
 * Seiten und Website-Bereiche, deren Texte im Admin-Bereich gepflegt werden.
 *
 * Der Wert steht in pages.key und ist zugleich der URL-Teil unter /admin/seiten/{page}.
 */
enum PageEnum: string
{
    case Home = 'startseite';
    case Contact = 'kontakt';
    case Social = 'social-media';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Startseite',
            self::Contact => 'Kontaktseite',
            self::Social => 'Social Media',
        };
    }

    /**
     * Öffentliche Route der Seite – die Social-Media-Links stehen im Footer jeder Seite.
     */
    public function routeName(): string
    {
        return match ($this) {
            self::Home, self::Social => 'home',
            self::Contact => 'contact',
        };
    }

    /**
     * Ausgangstexte, solange im Admin-Bereich nichts anderes gespeichert wurde.
     *
     * @return array<string, mixed>
     */
    public function defaults(): array
    {
        return match ($this) {
            self::Home => [
                'ticket_title' => 'Laut wird’s nur mit dir',
                'ticket_text' => 'Tribute-Nights, Rock-Legenden, Newcomer und Familienkonzerte – die besten Abende sind schnell ausverkauft. Sichere dir dein Ticket, solange es noch welche gibt.',
                'booking_title' => 'Band oder Künstler:in?',
                'booking_text' => 'Schick uns deine Booking-Anfrage.',
            ],
            self::Contact => [
                'hero_title' => 'Schreib Libby',
                'hero_lead' => 'Booking, Kooperation, Presse oder eine Frage zu deinem Ticket – wir lesen jede Nachricht.',
            ],
            self::Social => [
                'social_links' => array_values(array_filter([
                    config('libby.org.instagram') ? ['platform' => SocialPlatformEnum::Instagram->value, 'url' => config('libby.org.instagram')] : null,
                    config('libby.org.facebook') ? ['platform' => SocialPlatformEnum::Facebook->value, 'url' => config('libby.org.facebook')] : null,
                ])),
            ],
        };
    }
}
