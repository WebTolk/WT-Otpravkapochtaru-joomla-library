<?php

/**
 * Goods item payload line used inside an order `goods.items` section.
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

final class Item extends AbstractEntity
{
    /**
     * Store normalized goods item attributes.
     *
     * @since 3.0.0
     */
    private function __construct(private readonly array $attributes)
    {
    }

    /**
     * Hydrate a goods item and apply quantity/value/VAT defaults for incomplete input.
     *
     * @since 3.0.0
     */
    public static function fromArray(array $data): self
    {
        $attributes = self::normalizePayloadKeys($data) + [
            'quantity' => 1,
            'value'    => 0,
            'vat-rate' => -1,
        ];

        return new self($attributes);
    }

    /**
     * Export the goods item without null values.
     *
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return self::filterNullValues($this->attributes);
    }
}
