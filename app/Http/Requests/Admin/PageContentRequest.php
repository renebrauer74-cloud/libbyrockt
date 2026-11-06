<?php

namespace App\Http\Requests\Admin;

use App\Enums\Pages\PageEnum;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Texte einer gepflegten Seite – die Felder hängen von der Seite ab.
 */
class PageContentRequest extends AdminFormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->prepareSocialLinks();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->page()) {
            PageEnum::Home => [
                'ticket_title' => ['required', 'string', 'max:120'],
                'ticket_text' => ['nullable', 'string', 'max:600'],
                'booking_title' => ['required', 'string', 'max:120'],
                'booking_text' => ['nullable', 'string', 'max:300'],
            ],
            PageEnum::Contact => [
                'hero_title' => ['required', 'string', 'max:120'],
                'hero_lead' => ['nullable', 'string', 'max:400'],
            ],
            PageEnum::Social => $this->socialLinkRules(),
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'hero_title.required' => 'Bitte eine Überschrift eingeben.',
            'ticket_title.required' => 'Bitte eine Überschrift eingeben.',
            'booking_title.required' => 'Bitte eine Überschrift eingeben.',
            '*.max' => 'Der Text ist zu lang.',
            ...$this->socialLinkMessages(),
        ];
    }

    public function page(): PageEnum
    {
        return $this->route('page');
    }

    /**
     * Die Textfelder der Seite für pages.content.
     *
     * @return array<string, mixed>
     */
    public function pageContent(): array
    {
        if ($this->page() === PageEnum::Social) {
            return ['social_links' => $this->socialLinks()];
        }

        return $this->validated();
    }
}
