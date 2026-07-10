<?php

/**
 * Return-shipment address payload entity.
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

final class AddressReturn extends AbstractEntity
{
    /**
     * Store normalized address attributes.
     *
     * @since 3.0.0
     */
    private function __construct(private readonly array $attributes)
    {
    }

    /**
     * Hydrate a return address and default its API address type to `DEFAULT`.
     *
     * @since 3.0.0
     */
    public static function fromArray(array $data): self
    {
        $attributes = self::normalizePayloadKeys($data) + ['address-type' => 'DEFAULT'];

        return new self($attributes);
    }

    /**
     * Export a return address and require the minimal postal location fields.
     *
     * @since 3.0.0
     */
    public function toArray(): array
    {
        self::requireNonEmpty($this->attributes['index'] ?? null, 'Return address field "index" is required.');
        self::requireNonEmpty($this->attributes['place'] ?? null, 'Return address field "place" is required.');
        self::requireNonEmpty($this->attributes['region'] ?? null, 'Return address field "region" is required.');

        return self::filterNullValues($this->attributes);
    }
}
