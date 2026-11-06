<?php

namespace App\Actions\Messages;

use App\Enums\Messages\ContactTopicEnum;
use App\Enums\Messages\MessageTypeEnum;
use App\Models\Message;

/**
 * Die formularspezifischen Angaben einer Nachricht als „Bezeichnung => Wert“ für die Detailansicht.
 */
class DescribeMessageDetailsAction
{
    /**
     * @return array<string, string>
     */
    public function execute(Message $message): array
    {
        $details = $message->details ?? [];
        $value = fn (string $key): ?string => filled($details[$key] ?? null) ? (string) $details[$key] : null;

        $rows = match ($message->type) {
            MessageTypeEnum::Contact => [
                'Thema' => ContactTopicEnum::tryFrom((string) $value('topic'))?->label(),
            ],
        };

        return array_filter($rows, fn (?string $row): bool => filled($row));
    }
}
