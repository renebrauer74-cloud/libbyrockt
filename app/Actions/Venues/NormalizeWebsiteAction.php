<?php

namespace App\Actions\Venues;

/**
 * Macht aus einer frei eingetippten Website einen klickbaren Link.
 *
 * „turbinenhalle.de“, „www.turbinenhalle.de“, „http://turbinenhalle.de“ und Vertipper wie
 * „www.http://turbinenhalle.de“ oder „https//turbinenhalle.de“ werden zu einem Link mit http(s)://.
 */
class NormalizeWebsiteAction
{
    public function execute(string $website): string
    {
        // Leerzeichen gehören nie in eine Adresse
        $website = (string) preg_replace('/\s+/u', '', $website);

        if ($website === '') {
            return '';
        }

        // „www.http://…“ → „http://…“
        $website = (string) preg_replace('~^(?:www\.)+(?=https?(?::|/))~i', '', $website);

        $scheme = 'https';

        // Ein eingetipptes http(s) übernehmen – auch ohne Doppelpunkt oder mit zu vielen Schrägstrichen
        while (preg_match('~^(https?)(?::/*|/+)(.*)$~i', $website, $match)) {
            $scheme = strtolower($match[1]);
            $website = $match[2];
        }

        return $scheme.'://'.ltrim($website, '/');
    }

    /**
     * Sieht die bereinigte Adresse nach einer Website aus? Dafür genügt ein Punkt im Domainnamen.
     */
    public function looksValid(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && str_contains(trim($host, '.'), '.');
    }
}
