<?php

namespace App\Enums\Venues;

/**
 * Vorschläge für das Datenblatt einer Location – eigene Felder sind jederzeit zusätzlich möglich.
 */
enum VenueDetailEnum: string
{
    case Stage = 'buehne';
    case Capacity = 'kapazitaet';
    case Contact = 'ansprechpartner';
    case Phone = 'telefon';
    case Email = 'email';
    case Backline = 'backline';
    case Sound = 'ton';
    case Light = 'licht';
    case Power = 'strom';
    case LoadIn = 'anlieferung';
    case Parking = 'parken';
    case Backstage = 'backstage';
    case Catering = 'catering';
    case Hotel = 'hotel';
    case Notes = 'notizen';

    public function label(): string
    {
        return match ($this) {
            self::Stage => 'Bühnengröße',
            self::Capacity => 'Kapazität',
            self::Contact => 'Ansprechpartner:in',
            self::Phone => 'Telefon',
            self::Email => 'E-Mail',
            self::Backline => 'Backline',
            self::Sound => 'PA / Ton',
            self::Light => 'Licht',
            self::Power => 'Strom',
            self::LoadIn => 'Anlieferung / Load-in',
            self::Parking => 'Parken / Nightliner',
            self::Backstage => 'Backstage',
            self::Catering => 'Catering',
            self::Hotel => 'Hotel in der Nähe',
            self::Notes => 'Notizen',
        };
    }

    public function placeholder(): string
    {
        return match ($this) {
            self::Stage => 'z. B. 8 × 6 m, Höhe 1 m, Deckenhöhe 5 m',
            self::Capacity => 'z. B. 1.200 stehend / 600 bestuhlt',
            self::Contact => 'z. B. Name, Funktion',
            self::Phone => 'z. B. 0234 123456',
            self::Email => 'z. B. booking@location.de',
            self::Backline => 'z. B. Drumkit vorhanden, Amps mitbringen',
            self::Sound => 'z. B. Line-Array, Pult, Monitoring',
            self::Light => 'z. B. Movingheads, Hazer, Lichtpult',
            self::Power => 'z. B. 63 A CEE an der Bühne',
            self::LoadIn => 'z. B. Rampe hinten, Zufahrt über …',
            self::Parking => 'z. B. 2 Plätze für Nightliner, Strom vorhanden',
            self::Backstage => 'z. B. 2 Räume, Dusche, WLAN',
            self::Catering => 'z. B. Getränke frei, warmes Essen ab 18 Uhr',
            self::Hotel => 'z. B. Hotel am Bahnhof, 5 Min. zu Fuß',
            self::Notes => 'Alles, was sonst wichtig ist',
        };
    }

    /**
     * Vorschlag mit dieser Bezeichnung – null bei eigenen Feldern.
     */
    public static function fromLabel(string $label): ?self
    {
        $label = mb_strtolower(trim($label));

        foreach (self::cases() as $case) {
            if (mb_strtolower($case->label()) === $label) {
                return $case;
            }
        }

        return null;
    }
}
