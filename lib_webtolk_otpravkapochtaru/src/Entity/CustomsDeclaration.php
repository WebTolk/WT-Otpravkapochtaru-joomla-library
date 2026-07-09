<?php

/**
 * @package       WT Otpravkapochtaru
 * @version     3.0.0
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru\Entity;

defined('_JEXEC') or die;

use Webtolk\Otpravkapochtaru\Exception\ValidationException;

final class CustomsDeclaration extends AbstractEntity
{
    /**
     * @param list<CustomsDeclarationItem> $entries
     */
    private function __construct(
        private readonly array $attributes,
        private readonly array $entries,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $normalized     = self::normalizePayloadKeys($data);
        $entriesPayload = $normalized['customs-entries'] ?? [];
        $entries        = [];

        unset($normalized['customs-entries']);

        if (!is_array($entriesPayload)) {
            throw new ValidationException('Customs declaration field "customs-entries" must be an array.');
        }

        foreach ($entriesPayload as $entry) {
            if ($entry instanceof CustomsDeclarationItem) {
                $entries[] = $entry;

                continue;
            }

            if (!is_array($entry)) {
                throw new ValidationException('Customs declaration entries must contain arrays or CustomsDeclarationItem instances.');
            }

            $entries[] = CustomsDeclarationItem::fromArray($entry);
        }

        return new self(
            $normalized + [
                'currency'     => 'RUB',
                'entries-type' => 'GIFT',
            ],
            $entries,
        );
    }

    public function toArray(): array
    {
        $payload = self::filterNullValues($this->attributes);

        if ($this->entries !== []) {
            $payload['customs-entries'] = array_map(
                static fn (CustomsDeclarationItem $entry): array => $entry->toArray(),
                $this->entries
            );
        }

        return $payload;
    }
}
