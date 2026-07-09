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

final class Recipient extends AbstractEntity
{
    private function __construct(private readonly array $attributes)
    {
    }

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

    public function toArray(): array
    {
        return self::filterNullValues($this->attributes);
    }
}
