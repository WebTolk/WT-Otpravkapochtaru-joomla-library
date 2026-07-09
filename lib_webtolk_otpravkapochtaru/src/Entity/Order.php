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

final class Order extends AbstractEntity
{
    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $goodsAttributes
     * @param list<Item> $items
     */
    private function __construct(
        private readonly array $attributes,
        private readonly array $goodsAttributes,
        private readonly array $items,
        private readonly ?CustomsDeclaration $customsDeclaration,
        private readonly ?EcomData $ecomData,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $normalized      = self::normalizePayloadKeys($data);
        $goodsAttributes = [];
        $itemsPayload    = [];

        if (isset($normalized['goods'])) {
            if (!is_array($normalized['goods'])) {
                throw new ValidationException('Order field "goods" must be an array.');
            }

            $goodsAttributes = $normalized['goods'];
            $itemsPayload    = $goodsAttributes['items'] ?? [];
            unset($goodsAttributes['items'], $normalized['goods']);
        } elseif (isset($normalized['items'])) {
            $itemsPayload = $normalized['items'];
            unset($normalized['items']);
        }

        if (!is_array($itemsPayload)) {
            throw new ValidationException('Order goods items must be an array.');
        }

        $items = [];

        foreach ($itemsPayload as $item) {
            if ($item instanceof Item) {
                $items[] = $item;

                continue;
            }

            if (!is_array($item)) {
                throw new ValidationException('Order goods items must contain arrays or Item instances.');
            }

            $items[] = Item::fromArray($item);
        }

        $customsDeclaration = self::hydrateCustomsDeclaration($normalized['customs-declaration'] ?? null);
        $ecomData           = self::hydrateEcomData($normalized['ecom-data'] ?? null);

        unset($normalized['customs-declaration'], $normalized['ecom-data']);

        return new self(
            $normalized + [
                'address-type-to' => 'DEFAULT',
                'fragile'         => false,
                'mail-category'   => 'ORDINARY',
                'mail-direct'     => 643,
                'mail-type'       => 'POSTAL_PARCEL',
            ],
            $goodsAttributes,
            $items,
            $customsDeclaration,
            $ecomData,
        );
    }

    public function toArray(): array
    {
        if (($this->attributes['index-to'] ?? null) === null && ($this->attributes['str-index-to'] ?? null) === null) {
            throw new ValidationException('Order must contain either "index-to" or "str-index-to".');
        }

        $payload = self::filterNullValues($this->attributes);

        if ($this->goodsAttributes !== [] || $this->items !== []) {
            $payload['goods'] = self::filterNullValues($this->goodsAttributes);

            if ($this->items !== []) {
                $payload['goods']['items'] = array_map(
                    static fn (Item $item): array => $item->toArray(),
                    $this->items
                );
            }
        }

        if ($this->customsDeclaration instanceof CustomsDeclaration) {
            $payload['customs-declaration'] = $this->customsDeclaration->toArray();
        }

        if ($this->ecomData instanceof EcomData) {
            $payload['ecom-data'] = $this->ecomData->toArray();
        }

        return $payload;
    }

    private static function hydrateCustomsDeclaration(mixed $value): ?CustomsDeclaration
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CustomsDeclaration) {
            return $value;
        }

        if (!is_array($value)) {
            throw new ValidationException('Order field "customs-declaration" must be an array or CustomsDeclaration instance.');
        }

        return CustomsDeclaration::fromArray($value);
    }

    private static function hydrateEcomData(mixed $value): ?EcomData
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof EcomData) {
            return $value;
        }

        if (!is_array($value)) {
            throw new ValidationException('Order field "ecom-data" must be an array or EcomData instance.');
        }

        return EcomData::fromArray($value);
    }
}
