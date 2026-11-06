<?php

namespace App\Http\Controllers;

use App\Actions\Events\BuildEventCalendarAction;
use App\Actions\Events\CountUpcomingEventsAction;
use App\Actions\Venues\ListUpcomingVenuesAction;
use App\Enums\Events\EventCategoryEnum;
use App\Models\Event;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request, CountUpcomingEventsAction $countUpcomingEvents, ListUpcomingVenuesAction $listVenues, ?string $kategorie = null): View
    {
        $category = $kategorie !== null ? EventCategoryEnum::fromSlug($kategorie) : null;

        // Nur Städte, Locations und Genres, zu denen es kommende Termine gibt
        $cities = $listVenues->cities();
        $city = in_array($request->query('stadt'), $cities, true) ? $request->query('stadt') : null;

        $venues = $listVenues->execute();
        $venue = $venues->firstWhere('slug', $request->query('location'));

        $genres = Genre::query()
            ->whereHas('events', fn ($query) => $query->published()->upcoming())
            ->orderBy('name')
            ->get();

        $genre = $genres->firstWhere('slug', $request->query('genre'));

        $events = Event::query()
            ->published()
            ->upcoming()
            ->with(['media', 'genres', 'venue'])
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($venue, fn ($query) => $query->where('venue_id', $venue->getKey()))
            ->when($city && ! $venue, fn ($query) => $query->whereHas('venue', fn ($query) => $query->where('city', $city)))
            ->when($genre, fn ($query) => $query->whereHas('genres', fn ($query) => $query->whereKey($genre->getKey())))
            ->orderBy('starts_at')
            ->get();

        return view('events.index', [
            'category' => $category,
            'months' => $events->groupBy(fn (Event $event): string => $event->starts_at->format('Y-m')),
            'total' => $events->count(),
            'counts' => $countUpcomingEvents->execute(),
            'cities' => $cities,
            'city' => $venue?->city ?? $city,
            'venues' => $venues,
            'venue' => $venue,
            'genres' => $genres,
            'genre' => $genre,
        ]);
    }

    public function show(Event $event): View
    {
        $this->ensureVisible($event);
        $event->load(['genres', 'venue', 'organizer']);

        return view('events.show', [
            'event' => $event,
            'related' => Event::query()
                ->published()
                ->upcoming()
                ->with(['media', 'genres', 'venue'])
                ->whereKeyNot($event->getKey())
                ->orderByRaw('(category = ?) desc', [$event->category->value])
                ->orderBy('starts_at')
                ->limit(3)
                ->get(),
            // Weitere Termine derselben Serie
            'seriesDates' => $event->isSeries()
                ? $event->seriesEvents()->published()->upcoming()->with('venue')->whereKeyNot($event->getKey())->limit(6)->get()
                : collect(),
        ]);
    }

    public function calendar(Event $event, BuildEventCalendarAction $buildCalendar): Response
    {
        $this->ensureVisible($event);

        return response($buildCalendar->execute($event), 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"',
        ]);
    }

    /**
     * Entwürfe und noch nicht freigeschaltete geplante Veranstaltungen sehen nur eingeloggte Admins.
     */
    private function ensureVisible(Event $event): void
    {
        abort_if(! $event->isOnline() && ! auth()->check(), 404);
    }
}
