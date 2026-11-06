<?php

namespace App\Http\Requests\Admin;

use App\Enums\Social\SocialPlatformEnum;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Basis für die Admin-Formulare. Der Zugriff ist bereits per auth-Middleware geschützt.
 */
abstract class AdminFormRequest extends FormRequest
{
    /**
     * Höchstzahl der Social-Media-Links in einem Formular.
     */
    public const SOCIAL_LINK_SLOTS = 20;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Regeln für das Bild aus der Drag-&-Drop-Zone.
     *
     * @return list<string>
     */
    protected function imageRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'image',
            'mimes:jpg,jpeg,png,gif,webp,avif',
            'max:'.config('libby.uploads.max_kilobytes'),
            'dimensions:min_width=200,min_height=200',
        ];
    }

    /**
     * Regeln für ein Video (MP4, WebM oder MOV).
     *
     * @return list<string>
     */
    protected function videoRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'file',
            'mimetypes:video/mp4,application/mp4,video/webm,video/quicktime',
            'max:'.config('libby.uploads.video_max_kilobytes'),
        ];
    }

    /**
     * Location-Felder bereinigen: Leerraum zusammenfassen, Caps-Lock-ß, Website mit https://.
     */
    protected function prepareVenueFields(string $prefix = ''): void
    {
        $clean = [];

        foreach (['name', 'city', 'street', 'zip', 'website'] as $field) {
            if (is_string($this->input($prefix.$field))) {
                $clean[$prefix.$field] = trim((string) preg_replace('/\s+/u', ' ', $this->input($prefix.$field)));
            }
        }

        if (isset($clean[$prefix.'name'])) {
            $clean[$prefix.'name'] = normalize_caps($clean[$prefix.'name']);
        }

        $website = $clean[$prefix.'website'] ?? '';

        if ($website !== '' && ! preg_match('#^https?://#i', $website)) {
            $clean[$prefix.'website'] = 'https://'.$website;
        }

        $this->merge($clean);
    }

    /**
     * Regeln für eine Location (Verwaltung und „Neue Location“ im Veranstaltungsformular).
     *
     * @return array<string, array<mixed>>
     */
    protected function venueRules(string $prefix = ''): array
    {
        return [
            $prefix.'name' => ['required', 'string', 'max:120'],
            $prefix.'city' => ['required', 'string', 'max:80'],
            $prefix.'street' => ['nullable', 'string', 'max:150'],
            $prefix.'zip' => ['nullable', 'string', 'regex:/^[0-9A-Za-z -]{3,10}$/'],
            $prefix.'website' => ['nullable', 'url:http,https', 'max:300', function (string $attribute, mixed $value, Closure $fail): void {
                if (! str_contains((string) parse_url((string) $value, PHP_URL_HOST), '.')) {
                    $fail('Bitte einen gültigen Link angeben.');
                }
            }],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function venueMessages(string $prefix = ''): array
    {
        return [
            $prefix.'name.required' => 'Bitte den Namen der Location angeben.',
            $prefix.'name.max' => 'Maximal 120 Zeichen.',
            $prefix.'city.required' => 'Bitte die Stadt angeben.',
            $prefix.'city.max' => 'Maximal 80 Zeichen.',
            $prefix.'street.max' => 'Maximal 150 Zeichen.',
            $prefix.'zip.regex' => 'Ungültige Postleitzahl.',
            $prefix.'website.url' => 'Bitte einen gültigen Link angeben.',
            $prefix.'website.max' => 'Der Link ist zu lang.',
        ];
    }

    /**
     * Spalten der Tabelle venues aus den Formularfeldern – leere Felder werden null.
     *
     * @return array{name: string, city: string, street: string|null, zip: string|null, website: string|null}
     */
    protected function venueFields(string $prefix = ''): array
    {
        $value = fn (string $field): ?string => filled($this->input($prefix.$field)) ? (string) $this->input($prefix.$field) : null;

        return [
            'name' => (string) $value('name'),
            'city' => (string) $value('city'),
            'street' => $value('street'),
            'zip' => $value('zip'),
            'website' => $value('website'),
        ];
    }

    /**
     * Links ohne http(s):// bekommen https:// vorangestellt – z. B. „instagram.com/libbyrockt“.
     */
    protected function prepareSocialLinks(): void
    {
        $links = $this->input('social_links');

        if (! is_array($links)) {
            return;
        }

        $this->merge(['social_links' => array_map(function (mixed $link): mixed {
            if (! is_array($link)) {
                return $link;
            }

            $url = trim((string) ($link['url'] ?? ''));

            return [...$link, 'url' => $url !== '' && ! preg_match('#^https?://#i', $url) ? 'https://'.$url : $url];
        }, $links)]);
    }

    /**
     * Regeln für die Social-Media-Links (Plattform + Link, beliebig oft).
     *
     * @return array<string, array<mixed>>
     */
    protected function socialLinkRules(): array
    {
        return [
            'social_links' => ['nullable', 'array', 'max:'.self::SOCIAL_LINK_SLOTS],
            'social_links.*.platform' => ['required_with:social_links.*.url', Rule::enum(SocialPlatformEnum::class)],
            'social_links.*.url' => ['nullable', 'url:http,https', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function socialLinkMessages(): array
    {
        return [
            'social_links.max' => 'Bitte höchstens '.self::SOCIAL_LINK_SLOTS.' Links angeben.',
            'social_links.*.platform' => 'Bitte Instagram, Facebook, TikTok oder YouTube auswählen.',
            'social_links.*.url' => 'Bitte einen gültigen Link angeben.',
        ];
    }

    /**
     * Ausgefüllte Social-Media-Links in Formular-Reihenfolge, leere Zeilen fallen weg.
     *
     * @return list<array{platform: string, url: string}>
     */
    public function socialLinks(): array
    {
        return collect($this->input('social_links', []))
            ->filter(fn (mixed $link): bool => is_array($link) && filled($link['url'] ?? null))
            ->map(fn (array $link): array => ['platform' => (string) $link['platform'], 'url' => (string) $link['url']])
            ->values()
            ->all();
    }

    /**
     * Fehlermeldungen für Bild- und Video-Uploads der genannten Felder.
     *
     * @return array<string, string>
     */
    protected function uploadMessages(string ...$fields): array
    {
        $messages = [];

        foreach ($fields as $field) {
            $messages += [
                $field.'.image' => 'Bitte ein JPG-, PNG-, WebP-, GIF- oder AVIF-Bild hochladen.',
                $field.'.mimes' => 'Bitte ein JPG-, PNG-, WebP-, GIF- oder AVIF-Bild hochladen.',
                $field.'.mimetypes' => 'Bitte ein Video als MP4, WebM oder MOV hochladen.',
                $field.'.dimensions' => 'Das Bild ist zu klein (mindestens 200 × 200 Pixel).',
                $field.'.uploaded' => 'Die Datei ist zu groß oder wurde nicht vollständig hochgeladen.',
                $field.'.max' => 'Die Datei ist zu groß (Bilder max. '.(config('libby.uploads.max_kilobytes') / 1024)
                    .' MB, Videos max. '.(config('libby.uploads.video_max_kilobytes') / 1024).' MB).',
            ];
        }

        return $messages;
    }
}
