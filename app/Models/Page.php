<?php

namespace App\Models;

use App\Enums\Pages\PageEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Im Admin-Bereich gepflegte Seite (Startseite, Kontakt, Social Media) – Texte als JSON.
 */
#[Fillable(['key', 'content'])]
class Page extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => PageEnum::class,
            'content' => 'array',
        ];
    }
}
