<?php

namespace App\Actions\Events;

use App\Enums\Events\EventCategoryEnum;
use App\Enums\Events\TicketStatusEnum;
use App\Enums\MediaCollectionEnum;
use App\Enums\MediaConversionEnum;
use App\Models\Event;

/**
 * Strukturierte Daten (schema.org) für die Event-Detailseite – damit Google den Termin erkennt.
 */
class BuildEventStructuredDataAction
{
    public function __construct(private SummarizeEventDescriptionAction $summarize) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(Event $event): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => $event->category === EventCategoryEnum::Konzert ? 'MusicEvent' : 'Event',
            'name' => $event->title,
            'startDate' => $event->starts_at->toIso8601String(),
            'eventStatus' => 'https://schema.org/'.match ($event->ticket_status) {
                TicketStatusEnum::Cancelled => 'EventCancelled',
                TicketStatusEnum::Postponed => 'EventPostponed',
                default => 'EventScheduled',
            },
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => $event->venue ? [
                '@type' => 'Place',
                'name' => $event->venue->name,
                'address' => array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event->venue->street,
                    'postalCode' => $event->venue->zip,
                    'addressLocality' => $event->venue->city,
                    'addressCountry' => 'DE',
                ]),
            ] : null,
            'organizer' => [
                '@type' => 'Organization',
                'name' => config('libby.org.name'),
                'url' => config('app.url'),
            ],
            'url' => route('events.show', $event),
        ];

        $data = array_filter($data, fn (mixed $value): bool => $value !== null);

        if ($event->ends_at) {
            $data['endDate'] = $event->ends_at->toIso8601String();
        }

        $image = $event->getFirstMediaUrl(MediaCollectionEnum::Events->value, MediaConversionEnum::Large->value);
        if ($image !== '') {
            $data['image'] = [$image];
        }

        $description = $this->summarize->execute($event, 300);
        if ($description !== '') {
            $data['description'] = $description;
        }

        if ($event->ticket_url) {
            $data['offers'] = [
                '@type' => 'Offer',
                'url' => $event->ticket_url,
                'availability' => 'https://schema.org/'.($event->ticket_status === TicketStatusEnum::SoldOut ? 'SoldOut' : 'InStock'),
            ];
        }

        return $data;
    }
}
