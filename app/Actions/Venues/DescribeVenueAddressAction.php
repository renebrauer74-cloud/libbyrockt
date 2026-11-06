<?php

namespace App\Actions\Venues;

use App\Models\Venue;

/**
 * Anschrift und Routenplaner-Link einer Location.
 */
class DescribeVenueAddressAction
{
    /**
     * Einzeilige Adresse, z. B. „Musikweg 1, 46045 Oberhausen“.
     */
    public function execute(Venue $venue): string
    {
        return implode(', ', array_filter([$venue->street, trim($venue->zip.' '.$venue->city)]));
    }

    /**
     * Routenplaner-Link (Google Maps, ohne Einbettung).
     */
    public function routeUrl(Venue $venue): string
    {
        return 'https://www.google.com/maps/dir/?api=1&destination='
            .rawurlencode(trim($venue->name.', '.$this->execute($venue), ', '));
    }
}
