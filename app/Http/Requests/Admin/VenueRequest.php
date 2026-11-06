<?php

namespace App\Http\Requests\Admin;

use App\Actions\Venues\FindDuplicateVenueAction;
use App\Models\Venue;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Validator;

class VenueRequest extends AdminFormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareVenueFields();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->venueRules();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->venueMessages();
    }

    /**
     * Dieselbe Location darf es in einer Stadt nur einmal geben.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->hasAny(['name', 'city'])) {
                    return;
                }

                $current = $this->route('venue');
                $duplicate = app(FindDuplicateVenueAction::class)->execute(
                    (string) $this->input('name'),
                    (string) $this->input('city'),
                    $current instanceof Venue ? $current->getKey() : null,
                );

                if ($duplicate !== null) {
                    $validator->errors()->add('name', 'Diese Location gibt es in '.$duplicate->city.' schon.');
                }
            },
        ];
    }

    /**
     * Spalten der Tabelle venues aus den Formularfeldern.
     *
     * @return array{name: string, city: string, street: string|null, zip: string|null, website: string|null}
     */
    public function venueAttributes(): array
    {
        return $this->venueFields();
    }
}
