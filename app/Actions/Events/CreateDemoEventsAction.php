<?php

namespace App\Actions\Events;

use App\Actions\Venues\FindDuplicateVenueAction;
use App\Actions\Venues\SaveVenueAction;
use App\Enums\Events\EventCategoryEnum;
use App\Enums\Events\EventStatusEnum;
use App\Enums\Events\ImageFocusEnum;
use App\Enums\Events\TicketStatusEnum;
use App\Enums\MediaCollectionEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;

/**
 * Legt Start-Locations und neun Beispiel-Veranstaltungen mit Bildern aus database/demo/ an.
 * Erkennbar am Vermerk DEMO_MARK in der Beschreibung (siehe RemoveDemoEventsAction) – die Locations bleiben beim Entfernen erhalten.
 */
class CreateDemoEventsAction
{
    public const DEMO_MARK = '[Demo]';

    public function __construct(
        private GenerateEventSlugAction $generateSlug,
        private ResolveGenresAction $resolveGenres,
        private SaveVenueAction $saveVenue,
        private FindDuplicateVenueAction $findDuplicateVenue,
    ) {}

    /**
     * @return int Anzahl angelegter Veranstaltungen
     */
    public function execute(): int
    {
        $author = User::query()->oldest('id')->first();
        $venues = $this->venues();
        $description = "Hier steht die Beschreibung der Veranstaltung. Absätze, **fett** und Links wie https://libbyrockt.de werden automatisch formatiert.\n\nDies ist eine Beispiel-Veranstaltung. ".self::DEMO_MARK;

        foreach ($this->demoEvents() as [$title, $subtitle, $category, $label, $days, $time, $doors, $venue, $image, $ticketStatus, $featured, $presale, $box, $age]) {
            $event = new Event([
                'title' => $title,
                'subtitle' => $subtitle,
                'category' => $category,
                // Bei „Sonstiges“ die Art (z. B. Lesung), sonst werden daraus Genres
                'category_label' => $category === EventCategoryEnum::Sonstiges ? $label : null,
                'description' => $description,
                'starts_at' => today()->addDays($days)->setTimeFromTimeString($time),
                'doors_at' => $doors,
                'venue_id' => $venues[$venue]->getKey(),
                'image_focus' => ImageFocusEnum::Center,
                'ticket_url' => $ticketStatus->allowsTicketSale() ? (config('libby.org.tickets_url') ?: 'https://www.eventim.de/') : null,
                'price_presale' => $presale,
                'price_box' => $box,
                'age_rating' => $age,
                'ticket_status' => $ticketStatus,
                'status' => EventStatusEnum::Published,
                'is_featured' => $featured,
                'created_by' => $author?->getKey(),
            ]);
            $event->slug = $this->generateSlug->execute($event);
            $event->save();

            if ($category !== EventCategoryEnum::Sonstiges) {
                $event->genres()->sync($this->resolveGenres->execute([], preg_split('/\s*&\s*/', $label) ?: []));
            }

            if ($image !== null) {
                $event->addMedia(database_path('demo/'.$image))
                    ->preservingOriginal()
                    ->toMediaCollection(MediaCollectionEnum::Events->value);
            }
        }

        return count($this->demoEvents());
    }

    /**
     * Locations, in denen Libby Rockt! veranstaltet – bestehende werden wiederverwendet.
     *
     * @return array<string, Venue>
     */
    private function venues(): array
    {
        $venues = [
            'matrix' => ['Matrix', 'Bochum', 'Hauptstraße 200', '44892', 'https://www.matrix-bochum.de'],
            'zeche' => ['Zeche Bochum', 'Bochum', 'Prinz-Regent-Straße 50–60', '44795', 'https://www.zeche.com'],
            'turbinen' => ['Turbinenhalle', 'Oberhausen', 'Im Lipperfeld 23', '46047', 'https://www.turbinenhalle.de'],
            'essig' => ['Essigfabrik', 'Köln', 'Siegburger Straße 110', '50679', 'https://www.essig-fabrik.de'],
            'siegerland' => ['Siegerlandhalle', 'Siegen', 'Koblenzer Straße 151', '57072', 'https://www.siegerlandhalle-siegen.de'],
        ];

        return array_map(
            fn (array $venue): Venue => $this->findDuplicateVenue->execute($venue[0], $venue[1])
                ?? $this->saveVenue->execute(new Venue, array_combine(['name', 'city', 'street', 'zip', 'website'], $venue)),
            $venues,
        );
    }

    /**
     * @return list<array{string, string, EventCategoryEnum, string, int, string, ?string, string, ?string, TicketStatusEnum, bool, ?string, ?string, ?string}>
     */
    private function demoEvents(): array
    {
        return [
            ['Hybrid Theory Night', 'A Tribute to Linkin Park', EventCategoryEnum::Konzert, 'Tribute & Nu Metal', 5, '20:00', '19:00', 'essig', 'rockpalast.jpg', TicketStatusEnum::Few, true, '24 €', '29 €', null],
            ['Black Velvet', 'Support: The Rusty Nails', EventCategoryEnum::Konzert, 'Hard Rock', 9, '20:00', '19:00', 'matrix', 'tube.jpg', TicketStatusEnum::Available, true, '19 €', '23 €', null],
            ['Dino Rock für Kids', 'Das Mitmach-Rockkonzert für die ganze Familie', EventCategoryEnum::Konzert, 'Kinderkonzert & Metal', 12, '15:00', '14:00', 'turbinen', 'halle5.jpg', TicketStatusEnum::Available, true, '15 €', '18 €', 'ab 3 Jahren'],
            ['Violator Live', 'A Tribute to Depeche Mode', EventCategoryEnum::Konzert, 'Tribute & Synth Pop', 19, '20:00', '19:00', 'siegerland', 'katakomben.jpg', TicketStatusEnum::Available, false, '27 €', '32 €', null],
            ['Funk Soul Brothers', 'Funk · Soul · Rock – live', EventCategoryEnum::Konzert, 'Funk & Soul', 23, '20:00', '19:00', 'essig', null, TicketStatusEnum::SoldOut, false, '35 €', null, null],
            ['Pott Punk Festival', 'Sechs Bands, eine Bühne', EventCategoryEnum::Konzert, 'Punk', 30, '17:00', '16:00', 'zeche', 'bizarre-club.jpg', TicketStatusEnum::Available, false, '29 €', '34 €', null],
            ['Industrial Night', 'Live & DJ-Sets bis zum Morgen', EventCategoryEnum::Party, 'Industrial & EBM', 34, '21:00', null, 'matrix', 'aussen-2.jpg', TicketStatusEnum::Available, false, '15 €', '18 €', 'ab 18'],
            ['Libby Rockt! Weihnachts-Bash', 'Rock-Classics zum Jahresende', EventCategoryEnum::Party, 'Rock Classics', 41, '21:00', null, 'matrix', null, TicketStatusEnum::BoxOffice, false, null, '10 €', 'ab 18'],
            ['Unplugged im Turm', 'Akustik-Abend mit drei Songwriter:innen', EventCategoryEnum::Konzert, 'Akustik & Singer-Songwriter', 48, '19:30', '19:00', 'matrix', null, TicketStatusEnum::Available, false, '16 €', '19 €', null],
        ];
    }
}
