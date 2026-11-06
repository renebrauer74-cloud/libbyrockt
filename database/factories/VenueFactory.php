<?php

namespace Database\Factories;

use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Venue>
 */
class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company().' Halle';
        $city = fake()->randomElement(['Bochum', 'Dortmund', 'Essen', 'Oberhausen', 'Gelsenkirchen']);

        return [
            'name' => $name,
            'slug' => Str::slug($name.' '.$city),
            'city' => $city,
            'street' => fake()->streetAddress(),
            'zip' => fake()->postcode(),
            'website' => fake()->url(),
        ];
    }
}
