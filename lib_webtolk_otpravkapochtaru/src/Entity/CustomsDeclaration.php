<?php

/**
 * Customs declaration payload entity with optional declaration entries.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru\Entity;

defined('_JEXEC') or die;

use Webtolk\Otpravkapochtaru\Exception\ValidationException;

final class CustomsDeclaration extends AbstractEntity
{
    /**
     * Store declaration attributes and hydrated entry entities.
     *
     * @param list<CustomsDeclarationItem> $entries
     *
     * @since 3.0.0
     */
    private function __construct(
        private readonly array $attributes,
        private readonly array $entries,
    ) {
    }

    /**
     * Hydrate a customs declaration and normalize its `customs-entries` list.
     *
     * Missing currency and entry type are defaulted to RUB/GIFT for the common domestic setup.
     *
     * @since 3.0.0
     */
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

    /**
     * Export declaration attributes and nested entry payloads for the order API.
     *
     * @since 3.0.0
     */
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
