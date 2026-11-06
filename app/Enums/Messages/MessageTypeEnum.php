<?php

namespace App\Enums\Messages;

/**
 * Über welches Formular der Website eine Nachricht hereinkam – bei Libby Rockt! gibt es (noch) nur das Kontaktformular.
 */
enum MessageTypeEnum: string
{
    case Contact = 'contact';

    public function label(): string
    {
        return match ($this) {
            self::Contact => 'Kontakt',
        };
    }

    public function plural(): string
    {
        return match ($this) {
            self::Contact => 'Kontakt',
        };
    }

    /**
     * Symbol aus public/assets/img/icons.svg.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Contact => 'mail',
        };
    }
}
