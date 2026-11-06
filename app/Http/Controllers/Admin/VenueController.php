<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Venues\DeleteVenueAction;
use App\Actions\Venues\SaveVenueAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VenueRequest;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(): View
    {
        return view('admin.venues.index', [
            'venues' => Venue::query()
                ->withCount(['events', 'events as upcoming_count' => fn (Builder $query) => $query->published()->upcoming()])
                ->orderBy('city')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.venues.form', [
            'venue' => null,
            'events' => collect(),
            'cities' => $this->cities(),
        ]);
    }

    public function store(VenueRequest $request, SaveVenueAction $saveVenue): RedirectResponse
    {
        $venue = $saveVenue->execute(new Venue, $request->venueAttributes());

        return to_route('admin.venues.index')->with('success', '„'.$venue->name.'“ ('.$venue->city.') wurde angelegt.');
    }

    public function edit(Venue $venue): View
    {
        return view('admin.venues.form', [
            'venue' => $venue,
            'events' => $venue->events()->latest('starts_at')->get(),
            'cities' => $this->cities(),
        ]);
    }

    public function update(VenueRequest $request, Venue $venue, SaveVenueAction $saveVenue): RedirectResponse
    {
        $saveVenue->execute($venue, $request->venueAttributes());

        return to_route('admin.venues.index')->with('success', 'Änderungen an „'.$venue->name.'“ gespeichert.');
    }

    public function destroy(Venue $venue, DeleteVenueAction $deleteVenue): RedirectResponse
    {
        $detached = $deleteVenue->execute($venue);

        return to_route('admin.venues.index')->with('success', '„'.$venue->name.'“ wurde gelöscht.'
            .($detached > 0 ? ' '.$detached.' '.($detached === 1 ? 'Veranstaltung hat' : 'Veranstaltungen haben').' jetzt keine Location mehr.' : ''));
    }

    /**
     * Bereits verwendete Städte als Vorschläge.
     *
     * @return list<string>
     */
    private function cities(): array
    {
        return Venue::query()->distinct()->orderBy('city')->pluck('city')->all();
    }
}
