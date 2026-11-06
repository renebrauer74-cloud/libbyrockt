<?php

namespace App\Actions\Venues;

use App\Models\Venue;
use Illuminate\Support\Str;

/**
 * Gibt es eine Location mit diesem Namen schon in dieser Stadt? (Groß-/Kleinschreibung egal)
 */
class FindDuplicateVenueAction
{
    public function execute(string $name, string $city, ?int $ignoreId = null): ?Venue
    {
        return Venue::query()
            ->whereRaw('lower(name) = ?', [Str::lower(trim($name))])
            ->whereRaw('lower(city) = ?', [Str::lower(trim($city))])
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->first();
    }
}
