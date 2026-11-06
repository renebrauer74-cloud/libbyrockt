<?php

namespace App\Models;

use Database\Factories\VenueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Spielort (Club, Halle, Open-Air-Gelände) – wird einmal angelegt und danach bei jeder Veranstaltung nur noch ausgewählt.
 */
#[Fillable(['name', 'slug', 'city', 'street', 'zip', 'website', 'details'])]
class Venue extends Model
{
    /** @use HasFactory<VenueFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Datenblatt: Liste aus ['label' => …, 'value' => …]
            'details' => 'array',
        ];
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
