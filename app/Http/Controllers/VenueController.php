<?php

namespace App\Http\Controllers;

use App\Actions\Venues\ListUpcomingVenuesAction;
use Illuminate\View\View;

class VenueController extends Controller
{
    /**
     * Alle Locations mit kommenden Veranstaltungen, nach Städten gruppiert.
     */
    public function index(ListUpcomingVenuesAction $listVenues): View
    {
        return view('pages.venues', [
            'cities' => $listVenues->execute()->groupBy('city'),
        ]);
    }
}
