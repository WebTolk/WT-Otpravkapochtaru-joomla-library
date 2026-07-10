<?php

/**
 * Recipient reliability-check payload entity.
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

final class Recipient extends AbstractEntity
{
    /**
     * Store normalized recipient reliability attributes.
     *
     * @since 3.0.0
     */
    private function __construct(private readonly array $attributes)
    {
    }

    /**
     * Hydrate recipient data and map common aliases to Russian Post `raw-*` fields.
     *
     * @since 3.0.0
     */
    public static function fromArray(array $data): self
    {
        $normalized = self::normalizePayloadKeys($data);

        return new self(
            [
                'raw-address'   => $normalized['raw-address'] ?? $normalized['address'] ?? null,
                'raw-full-name' => $normalized['raw-full-name'] ?? $normalized['name'] ?? null,
                'raw-telephone' => $normalized['raw-telephone'] ?? $normalized['phone'] ?? null,
            ]
        );
    }

    /**
     * Export recipient reliability data without null aliases.
     *
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return self::filterNullValues($this->attributes);
    }
}
