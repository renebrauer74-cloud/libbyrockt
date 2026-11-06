<?php

namespace Database\Factories;

use App\Enums\Messages\ContactTopicEnum;
use App\Enums\Messages\MessageStatusEnum;
use App\Enums\Messages\MessageTypeEnum;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => MessageTypeEnum::Contact,
            'status' => MessageStatusEnum::New,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraphs(2, true),
            'details' => ['topic' => fake()->randomElement(ContactTopicEnum::cases())->value],
        ];
    }

    public function booking(): static
    {
        return $this->state(fn (array $attributes) => [
            'subject' => 'Booking-Anfrage: '.fake()->words(2, true),
            'details' => ['topic' => ContactTopicEnum::Booking->value],
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['read_at' => now()]);
    }

    public function done(): static
    {
        return $this->state(fn (array $attributes) => ['status' => MessageStatusEnum::Done, 'read_at' => now()]);
    }
}
