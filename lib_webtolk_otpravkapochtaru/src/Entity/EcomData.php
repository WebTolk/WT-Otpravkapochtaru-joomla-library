<?php

/**
 * E-commerce payment and delivery data payload section.
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

final class EcomData extends AbstractEntity
{
    /**
     * Store normalized e-commerce attributes.
     *
     * @since 3.0.0
     */
    private function __construct(private readonly array $attributes)
    {
    }

    /**
     * Hydrate e-commerce data and default delivery price/VAT fields expected by the API.
     *
     * @since 3.0.0
     */
    public static function fromArray(array $data): self
    {
        $attributes = self::normalizePayloadKeys($data) + [
            'delivery-rate'     => 0,
            'delivery-vat-rate' => -1,
        ];

        return new self($attributes);
    }

    /**
     * Export e-commerce data without null values.
     *
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return self::filterNullValues($this->attributes);
    }
}
