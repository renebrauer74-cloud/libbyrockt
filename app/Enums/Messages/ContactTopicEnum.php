<?php

namespace App\Enums\Messages;

/**
 * Themen-Auswahl im Kontaktformular – per ?thema=booking vorauswählbar.
 */
enum ContactTopicEnum: string
{
    case General = 'allgemein';
    case Booking = 'booking';
    case Venue = 'location';
    case Tickets = 'tickets';
    case Press = 'presse';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Allgemeine Frage',
            self::Booking => 'Booking – Band / Künstler:in',
            self::Venue => 'Location / Kooperation',
            self::Tickets => 'Tickets & Gästeliste',
            self::Press => 'Presse & Akkreditierung',
        };
    }
}
