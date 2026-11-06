<?php

namespace App\Actions\Venues;

use App\Models\Venue;
use Illuminate\Support\Facades\DB;

/**
 * Löscht eine Location – ihre Veranstaltungen bleiben erhalten, stehen danach aber ohne Location da.
 */
class DeleteVenueAction
{
    /**
     * @return int Anzahl der Veranstaltungen, die nun keine Location mehr haben
     */
    public function execute(Venue $venue): int
    {
        return DB::transaction(function () use ($venue): int {
            $detached = $venue->events()->update(['venue_id' => null]);
            $venue->delete();

            return $detached;
        });
    }
}
