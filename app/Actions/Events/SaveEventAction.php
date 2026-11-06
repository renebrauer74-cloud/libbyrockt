<?php

namespace App\Actions\Events;

use App\Actions\Venues\FindDuplicateVenueAction;
use App\Actions\Venues\SaveVenueAction;
use App\Enums\MediaCollectionEnum;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Legt eine Veranstaltung an oder aktualisiert sie – inklusive Slug, Location, Genres, Bild und Video.
 */
class SaveEventAction
{
    public function __construct(
        private GenerateEventSlugAction $generateSlug,
        private SaveVenueAction $saveVenue,
        private FindDuplicateVenueAction $findDuplicateVenue,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes  Spalten aus EventRequest::eventAttributes()
     * @param  array{name: string, city: string, street: string|null, zip: string|null, website: string|null}|null  $newVenue  Im Formular neu eingetragene Location – gibt es sie schon, wird die bestehende genommen
     * @param  list<int>|null  $genreIds  null = Genres unverändert lassen
     * @param  Event|null  $template  „Erneut ansetzen“: Bild und Video dieser Veranstaltung übernehmen, sofern nichts Neues hochgeladen wurde
     */
    public function execute(
        Event $event,
        array $attributes,
        ?UploadedFile $image,
        bool $removeImage,
        ?User $author,
        ?array $genreIds = null,
        ?UploadedFile $video = null,
        bool $removeVideo = false,
        ?Event $template = null,
        ?array $newVenue = null,
    ): Event {
        DB::transaction(function () use ($event, $attributes, $author, $genreIds, $newVenue): void {
            $event->fill($attributes);

            if ($newVenue !== null) {
                $venue = $this->findDuplicateVenue->execute($newVenue['name'], $newVenue['city'])
                    ?? $this->saveVenue->execute(new Venue, $newVenue);
                $event->venue()->associate($venue);
            }

            $event->slug = $this->generateSlug->execute($event);

            if (! $event->exists) {
                $event->created_by = $author?->getKey();
            }

            $event->save();

            if ($genreIds !== null) {
                $event->genres()->sync($genreIds);
            }
        });

        $this->saveMedia($event, MediaCollectionEnum::Events, $image, $removeImage, $template);
        $this->saveMedia($event, MediaCollectionEnum::EventVideo, $video, $removeVideo, $template);

        return $event;
    }

    private function saveMedia(Event $event, MediaCollectionEnum $collection, ?UploadedFile $file, bool $remove, ?Event $template): void
    {
        if ($file !== null) {
            $event->addMedia($file)->toMediaCollection($collection->value);
        } elseif ($remove) {
            $event->clearMediaCollection($collection->value);
        } elseif ($template !== null && ! $event->hasMedia($collection->value)) {
            $template->getFirstMedia($collection->value)?->copy($event, $collection->value);
        }
    }
}
