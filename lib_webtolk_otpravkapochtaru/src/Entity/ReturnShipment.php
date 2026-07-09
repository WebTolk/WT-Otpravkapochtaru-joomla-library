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

final class ReturnShipment extends AbstractEntity
{
    private function __construct(
        private readonly array $attributes,
        private readonly ?AddressReturn $addressFrom,
        private readonly ?AddressReturn $addressTo,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $normalized  = self::normalizePayloadKeys($data);
        $addressFrom = self::hydrateAddress($normalized['address-from'] ?? null, 'address-from');
        $addressTo   = self::hydrateAddress($normalized['address-to'] ?? null, 'address-to');

        unset($normalized['address-from'], $normalized['address-to']);

        return new self($normalized, $addressFrom, $addressTo);
    }

    public function toArray(): array
    {
        self::requireNonEmpty($this->attributes['mail-type'] ?? null, 'Return shipment field "mail-type" is required.');
        self::requireNonEmpty($this->attributes['recipient-name'] ?? null, 'Return shipment field "recipient-name" is required.');
        self::requireNonEmpty($this->attributes['sender-name'] ?? null, 'Return shipment field "sender-name" is required.');

        if (!$this->addressFrom instanceof AddressReturn) {
            throw new ValidationException('Return shipment field "address-from" must be provided.');
        }

        $payload                 = self::filterNullValues($this->attributes);
        $payload['address-from'] = $this->addressFrom->toArray();

        if ($this->addressTo instanceof AddressReturn) {
            $payload['address-to'] = $this->addressTo->toArray();
        }

        return $payload;
    }

    private static function hydrateAddress(mixed $value, string $field): ?AddressReturn
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof AddressReturn) {
            return $value;
        }

        if (!is_array($value)) {
            throw new ValidationException(sprintf('Return shipment field "%s" must be an array or AddressReturn instance.', $field));
        }

        return AddressReturn::fromArray($value);
    }
}
