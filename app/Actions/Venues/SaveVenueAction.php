<?php

namespace App\Actions\Venues;

use App\Models\Venue;
use Illuminate\Support\Str;

/**
 * Legt eine Location an oder aktualisiert sie – mit eindeutigem Slug aus Name und Stadt.
 */
class SaveVenueAction
{
    /**
     * @param  array{name: string, city: string, street?: string|null, zip?: string|null, website?: string|null}  $attributes
     */
    public function execute(Venue $venue, array $attributes): Venue
    {
        $venue->fill($attributes);
        $venue->slug = $this->uniqueSlug($venue);
        $venue->save();

        return $venue;
    }

    /**
     * Name + Stadt – die Stadt nur, wenn sie nicht schon im Namen steht („Zeche Bochum“ → zeche-bochum).
     */
    private function uniqueSlug(Venue $venue): string
    {
        $name = $this->slug($venue->name);
        $city = $this->slug($venue->city);
        $base = Str::limit($city !== '' && ! str_contains($name, $city) ? $name.'-'.$city : $name, 130, '');
        $base = trim($base, '-') ?: 'location';
        $slug = $base;

        for ($suffix = 2; $this->isTaken($slug, $venue); $suffix++) {
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    private function slug(string $text): string
    {
        return Str::slug($text, '-', 'de', ['&' => 'und', '@' => 'at']);
    }

    private function isTaken(string $slug, Venue $venue): bool
    {
        return Venue::query()
            ->where('slug', $slug)
            ->when($venue->exists, fn ($query) => $query->whereKeyNot($venue->getKey()))
            ->exists();
    }
}
