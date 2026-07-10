<?php

/**
 * One item line inside a customs declaration payload.
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

final class CustomsDeclarationItem extends AbstractEntity
{
    /**
     * Store normalized customs entry attributes.
     *
     * @since 3.0.0
     */
    private function __construct(private readonly array $attributes)
    {
    }

    /**
     * Hydrate a declaration item and apply Russian Post defaults for missing numeric/text fields.
     *
     * @since 3.0.0
     */
    public static function fromArray(array $data): self
    {
        $attributes = self::normalizePayloadKeys($data) + [
            'amount'       => 0,
            'country-code' => 643,
            'description'  => '',
            'tnved-code'   => '',
            'trademark'    => '',
            'weight'       => 0,
        ];

        return new self($attributes);
    }

    /**
     * Export the declaration item without null values.
     *
     * @since 3.0.0
     */
    public function toArray(): array
    {
        return self::filterNullValues($this->attributes);
    }
}
