<?php

namespace App\Http\Controllers;

use App\Actions\Events\CountUpcomingEventsAction;
use App\Actions\Venues\ListUpcomingVenuesAction;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CountUpcomingEventsAction $countUpcomingEvents, ListUpcomingVenuesAction $listVenues): View
    {
        $upcoming = fn () => Event::query()->published()->upcoming()->with(['media', 'genres', 'venue']);

        return view('home', [
            // Highlights im eingestellten Zeitraum zuerst, danach mit den nächsten Terminen aufgefüllt
            'slides' => $upcoming()
                ->highlightsFirst()
                ->orderBy('starts_at')
                ->limit(config('libby.home.slides'))
                ->get(),
            'events' => $upcoming()
                ->orderBy('starts_at')
                ->limit(config('libby.home.events'))
                ->get(),
            'counts' => $countUpcomingEvents->execute(),
            'venues' => $listVenues->execute(),
        ]);
    }
}
