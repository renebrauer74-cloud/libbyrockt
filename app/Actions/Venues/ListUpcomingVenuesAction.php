<?php

namespace App\Actions\Venues;

use App\Models\Event;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Locations und Städte, in denen demnächst etwas stattfindet – für die Locations-Seite und die Filter im Programm.
 */
class ListUpcomingVenuesAction
{
    /**
     * Locations mit kommenden Veranstaltungen (Anzahl als upcoming_count), nach Stadt und Name sortiert.
     *
     * @return Collection<int, Venue>
     */
    public function execute(): Collection
    {
        return Venue::query()
            ->whereHas('events', $this->upcoming(...))
            ->withCount(['events as upcoming_count' => $this->upcoming(...)])
            ->withMin(['events as next_starts_at' => $this->upcoming(...)], 'starts_at')
            ->orderBy('city')
            ->orderBy('name')
            ->get();
    }

    /**
     * Städte mit kommenden Veranstaltungen.
     *
     * @return list<string>
     */
    public function cities(): array
    {
        return Venue::query()
            ->whereHas('events', $this->upcoming(...))
            ->distinct()
            ->orderBy('city')
            ->pluck('city')
            ->all();
    }

    /**
     * @param  Builder<Event>  $query
     */
    private function upcoming(Builder $query): void
    {
        $query->published()->upcoming();
    }
}
