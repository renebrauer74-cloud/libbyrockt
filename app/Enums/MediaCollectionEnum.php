<?php

namespace App\Enums;

/**
 * Media-Collections – der Wert ist zugleich der Unterordner auf der Disk (siehe CollectionPathGenerator).
 */
enum MediaCollectionEnum: string
{
    case Events = 'events';

    /** Eigenes Video einer Veranstaltung (Detailseite) */
    case EventVideo = 'event-videos';

    /** Anhänge aus dem Kontaktformular (z. B. Pressekit) – private Disk */
    case MessageAttachments = 'message-attachments';

    /**
     * Collections mit Videos statt Bildern.
     */
    public function holdsVideos(): bool
    {
        return $this === self::EventVideo;
    }
}
