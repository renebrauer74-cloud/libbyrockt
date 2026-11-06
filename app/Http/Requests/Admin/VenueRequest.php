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
     * So viele Angaben passen ins Datenblatt einer Location.
     */
    public const MAX_DETAILS = 40;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareVenueFields();

        // Datenblatt: Zeilen ohne Angabe fallen weg – z. B. ein angeklicktes, aber nicht ausgefülltes Feld
        $details = [];

        foreach ((array) $this->input('details', []) as $row) {
            $label = trim((string) preg_replace('/\s+/u', ' ', (string) ($row['label'] ?? '')));
            $value = trim(str_replace("\r\n", "\n", (string) ($row['value'] ?? '')));

            if ($value !== '') {
                $details[] = ['label' => $label, 'value' => $value];
            }
        }

        $this->merge(['details' => $details]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->venueRules(),
            'details' => ['array', 'max:'.self::MAX_DETAILS],
            'details.*.label' => ['required', 'string', 'max:60'],
            'details.*.value' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->venueMessages(),
            'details.max' => 'Höchstens '.self::MAX_DETAILS.' Angaben im Datenblatt.',
            'details.*.label.required' => 'Bitte eine Bezeichnung eintragen, z. B. „Bühnengröße“.',
            'details.*.label.max' => 'Die Bezeichnung darf höchstens 60 Zeichen lang sein.',
            'details.*.value.max' => 'Höchstens 2.000 Zeichen.',
        ];
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
     * @return array{name: string, city: string, street: string|null, zip: string|null, website: string|null, details: list<array{label: string, value: string}>|null}
     */
    public function venueAttributes(): array
    {
        return [
            ...$this->venueFields(),
            'details' => array_values((array) $this->validated('details', [])) ?: null,
        ];
    }
}
