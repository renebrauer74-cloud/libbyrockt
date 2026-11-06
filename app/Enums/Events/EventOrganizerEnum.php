<?php

namespace App\Enums\Events;

/**
 * Wer veranstaltet? Libby Rockt! selbst oder ein externer Veranstalter (dann mit Stammdaten in organizers).
 */
enum EventOrganizerEnum: string
{
    case Libby = 'libby';
    case External = 'external';

    public function label(): string
    {
        return match ($this) {
            self::Libby => 'Libby Rockt!',
            self::External => 'Externer Veranstalter',
        };
    }
}
