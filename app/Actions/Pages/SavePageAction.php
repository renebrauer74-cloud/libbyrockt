<?php

namespace App\Actions\Pages;

use App\Models\Page;

/**
 * Speichert die Texte einer gepflegten Seite.
 */
class SavePageAction
{
    /**
     * @param  array<string, mixed>  $content  Felder aus PageContentRequest::pageContent()
     */
    public function execute(Page $page, array $content): Page
    {
        $page->content = array_replace($page->content ?? [], $content);
        $page->save();

        return $page;
    }
}
